<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\Video;
use App\Models\Quiz;
use App\Models\WebsiteLockSetting;
use App\Models\SuperuserRecoveryCode;
use Carbon\Carbon;
use App\Services\EmailVerificationService;
use App\Services\UserActivityService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\ResetPasswordMail;
use App\Mail\GoogleAccountInfoMail;
use App\Mail\PasswordChangedMail;

class AuthController extends Controller
{
    protected $emailVerifier;

    // Add these constants for clearer error categorization
    const ERROR_CATEGORIES = [
        'RATE_LIMIT' => 'rate_limit',
        'EMAIL_EXISTS' => 'email_exists',
        'INVALID_CREDENTIALS' => 'invalid_credentials',
        'ACCOUNT_LOCKED' => 'account_locked',
        'INVALID_EMAIL' => 'invalid_email',
        'INVALID_PHONE' => 'invalid_phone',
        'EMAIL_VERIFICATION_FAILED' => 'email_verification_failed',
        'PASSWORD_VALIDATION' => 'password_validation',
        'GOOGLE_AUTH' => 'google_auth',
        'VALIDATION' => 'validation',
        'SYSTEM' => 'system'
    ];

    public function __construct()
    {
        $this->emailVerifier = new EmailVerificationService();
    }
    /**
     * Maximum login attempts before lockout
     */
    const MAX_LOGIN_ATTEMPTS = 5;
    
    /**
     * Lockout duration in minutes
     */
    const LOCKOUT_DURATION = 15;

    public function showUnlock()
    {
        return view('auth.unlock');
    }

    public function showSuspended(Request $request)
    {
        $userEmail = $request->query('email');
        $suspensionReason = $request->query('reason');

        return view('auth.suspended', compact('userEmail', 'suspensionReason'));
    }

    public function unlock(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'recovery_code' => 'nullable|string'
        ]);

        $user = User::where('email', $request->email)
                    ->where('is_superuser', true)
                    ->first();

        // Check if user exists and is a superuser
        if (!$user || !$user->is_superuser) {
            return back()->withErrors([
                'email' => 'Access denied. This user is not allowed to unlock the website.',
            ])->withInput($request->except('password', 'recovery_code'));
        }

        if (!$user) {
            return back()->withErrors([
                'email' => 'No superuser found with this email address.',
            ]);
        }
        
        // Check recovery code first if provided
        if ($request->filled('recovery_code')) {
            // Get the recovery code instance instead of just checking existence
            $recoveryCode = SuperuserRecoveryCode::where('code', $request->recovery_code)
                ->where('user_id', $user->id)
                ->first();
                
            if ($recoveryCode) {
                // Log the recovery code usage
                Log::channel('security')->info('recovery_code_used', [
                    'user_id' => $user->id,
                    'code' => $request->recovery_code,
                    'ip' => $request->ip(),
                ]);
                // Delete the recovery code after successful use
                $recoveryCode->delete();
                
                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->intended('/');
            }
            
            return back()->withErrors([
                'recovery_code' => 'Invalid recovery code',
            ]);
        }

        // Regular password check
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            
            Log::channel('security')->info('Session regenerated', [
                'session_id' => session()->getId()
            ]);

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Invalid superuser credentials',
        ]);
    }

    public function showLogin(Request $request)
    {
        // Check if user is already authenticated
        if (Auth::check()) {
            $this->logAuthEvent('authenticated_user_accessed_login', 'info', $request);

            $user = Auth::user();

            Log::info('User already authenticated on login page', [
                'user_id' => $user->id,
                'email' => $user->email,
                'has_remember_token' => !empty($user->remember_token),
                'session_id' => $request->session()->getId(),
                'via_remember' => Auth::viaRemember()
            ]);

            // Check if user is admin and redirect appropriately
            if ($user->is_admin || $user->is_superuser) {
                // For admin users, check if they were trying to access admin panel
                $intendedUrl = session('url.intended');
                if ($intendedUrl && str_contains($intendedUrl, '/admin')) {
                    return redirect()->intended(route('admin.dashboard'));
                }
                // If no admin-specific intended URL, go to admin dashboard
                return redirect()->route('admin.dashboard');
            }

            // For regular users
            // For regular users - always redirect to main dashboard
            return redirect()->route('dashboard.main');
        }

        // Check for too many failed attempts
        $key = $this->throttleKey($request);
        if (RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            $this->logAuthEvent('login_rate_limit_exceeded', self::ERROR_CATEGORIES['RATE_LIMIT'], $request, [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'lockout_seconds' => $seconds
            ]);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $key = $this->throttleKey($request);

        // Check rate limiting
        if (RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            $this->logAuthEvent('rate_limit_exceeded', self::ERROR_CATEGORIES['RATE_LIMIT'], $request, [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'lockout_seconds' => $seconds,
                'attempts' => RateLimiter::attempts($key)
            ]);

            return redirect()->route('login')->withErrors([
                'rate_limit' => "Too many login attempts. Please try again in " . ceil($seconds / 60) . " minutes.",
            ])->withInput($request->except('password'));
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255'
            ],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address is too long.',
            'password.required' => 'Please enter your password.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password is too long.',
        ]);

        if ($validator->fails()) {
            RateLimiter::hit($key, self::LOCKOUT_DURATION * 60);

            $this->logAuthEvent('validation_failed', self::ERROR_CATEGORIES['VALIDATION'], $request, [
                'errors' => $validator->errors()->toArray(),
                'email' => $request->input('email'),
                'ip' => $request->ip()
            ]);

            return redirect()->route('login')->withErrors($validator)->withInput($request->except('password'));
        }

        $credentials = $validator->validated();
        $credentials['email'] = strtolower(trim($credentials['email']));

        // Check if user exists
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            RateLimiter::hit($key, self::LOCKOUT_DURATION * 60);

            $this->logAuthEvent('user_not_found', self::ERROR_CATEGORIES['INVALID_CREDENTIALS'], $request, [
                'email' => $credentials['email'],
                'ip' => $request->ip()
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'No account found with this email address.',
            ])->withInput($request->except('password'));
        }

        // Check if user account is locked
        if ($user->locked_until && Carbon::now()->lt($user->locked_until)) {
            $this->logAuthEvent('account_locked', self::ERROR_CATEGORIES['ACCOUNT_LOCKED'], $request, [
                'user_id' => $user->id,
                'email' => $credentials['email'],
                'locked_until' => $user->locked_until,
                'failed_attempts' => $user->failed_login_attempts
            ]);

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been temporarily locked due to too many failed attempts. Please try again later or reset your password.',
            ])->withInput($request->except('password'));
        }

        // Check if user account is suspended
        if ($user->isSuspended()) {
            $this->logAuthEvent('account_suspended', 'account_suspended', $request, [
                'user_id' => $user->id,
                'email' => $credentials['email'],
                'suspended_at' => $user->suspended_at,
                'suspension_reason' => $user->suspension_reason
            ]);

            // Redirect to suspended account page with user details
            return redirect()->route('auth.suspended', [
                'email' => $credentials['email'],
                'reason' => $user->suspension_reason
            ]);
        }

        // Attempt authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Clear rate limiting on successful login
            RateLimiter::clear($key);

            // Reset failed login attempts
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => Carbon::now(),
                'last_login_ip' => $request->ip(),
            ]);

            $this->logAuthEvent('successful_login', 'success', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
                'remember_me' => $request->boolean('remember')
            ]);

            // Log user activity
            UserActivityService::logActivity(
                'user_login',
                'User logged in successfully',
                [
                    'remember_me' => $request->boolean('remember'),
                    'login_method' => 'email'
                ],
                $user->id,
                get_client_ip(),
                $request->userAgent()
            );

            event(new Login('web', $user, false));

            // Check if user is admin and redirect appropriately
            if ($user->is_admin || $user->is_superuser) {
                $intendedUrl = session('url.intended');
                if ($intendedUrl && str_contains($intendedUrl, '/admin')) {
                    return redirect()->intended(route('admin.dashboard'));
                }
                return redirect()->route('admin.dashboard');
            }

            // Check database for existing progress to set session
            $latestProgress = UserProgress::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if ($latestProgress) {
                // Restore their active session
                session(['selected_level_group' => $latestProgress->level_group]);
            } else {
                // Also check for any progress if no active one found
                $anyProgress = UserProgress::where('user_id', $user->id)->latest()->first();
                if ($anyProgress) {
                     session(['selected_level_group' => $anyProgress->level_group]);
                }
            }

            return redirect()->route('dashboard.main');
        }

        // Failed login attempt
        RateLimiter::hit($key, self::LOCKOUT_DURATION * 60);

        // Increment failed attempts
        $failedAttempts = $user->failed_login_attempts + 1;
        $lockUntil = null;

        // Lock account after 5 failed attempts
        if ($failedAttempts >= 5) {
            $lockUntil = Carbon::now()->addMinutes(30);
        }

        $user->update([
            'failed_login_attempts' => $failedAttempts,
            'locked_until' => $lockUntil,
            'last_login_ip' => get_client_ip(),
        ]);

        $this->logAuthEvent('failed_login', self::ERROR_CATEGORIES['INVALID_CREDENTIALS'], $request, [
            'user_id' => $user->id,
            'email' => $credentials['email'],
            'failed_attempts' => $failedAttempts,
            'ip' => get_client_ip(),
            'is_locked' => $lockUntil !== null
        ]);

        event(new Failed('web', $user, $credentials));

        // Return with password error message
        return redirect()
            ->route('login')
            ->withErrors([
                'password' => 'The password you entered is incorrect.',
                'auth_failed' => true  // Additional flag to ensure error display
            ])
            ->withInput($request->except('password'));
    }

    public function showSignup(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user is admin and redirect appropriately
            if ($user->is_admin || $user->is_superuser) {
                return redirect()->route('admin.dashboard');
            }
            
            // For regular users
            // For regular users - always redirect to main dashboard
            return redirect()->route('dashboard.main');
        }
        return view('auth.signup');
    }

    
    
    public function signup(Request $request)
    {
        // Rate limit by IP only (not email) to avoid issues with existing emails
        $rateLimitKey = 'signup:ip:' . get_client_ip();

        $maxAttempts = 5;
        $decaySeconds = 600; // 10 minutes

        // Check rate limiting before validation
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            $this->logAuthEvent('signup_rate_limit', self::ERROR_CATEGORIES['RATE_LIMIT'], $request, [
                'email' => $request->input('email'),
                'ip' => get_client_ip(),
                'lockout_seconds' => $seconds
            ]);

            return back()->withErrors([
                'auth_error' => "Too many signup attempts. Please try again in " . ceil($seconds / 60) . " minutes.",
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        // Validate all inputs
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z\s\-\'\.]+$/'
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users'
            ],
            'country' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/'
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\+?[0-9\s\-\(\)]{7,20}$/',
                'unique:users'
            ],
            'country_code' => [
                'nullable',
                'string',
                'max:5',
                'regex:/^\+[0-9]{1,4}$/'
            ],
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(3)
            ],
        ], [
            'name.required' => 'Please enter your full name.',
            'name.min' => 'Name must be at least 2 characters long.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.required' => 'Please enter your email address.',
            'email.unique' => 'An account with this email already exists. Please <a href="' . route('login') . '" class="text-primary-blue hover:underline">login</a> or use a different email.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address is too long.',
            'phone.unique' => 'This phone number is already registered. Please use a different number or <a href="' . route('login') . '" class="text-primary-blue hover:underline">login instead</a>.',
            'phone.regex' => 'Please enter a valid phone number.',
            'country.required' => 'Please select your country.',
            'country.regex' => 'Country name can only contain letters, spaces, and hyphens.',
            'country_code.regex' => 'Please select a valid country code.',
            'password.required' => 'Please create a strong password.',
            'password.uncompromised' => 'This password has been found in data breaches. Please choose a more secure password.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            // Don't hit rate limiter for validation errors (especially unique constraints)
            // This prevents legitimate users from being blocked when they accidentally use existing credentials

            $this->logAuthEvent('signup_validation_failed', $this->determineErrorType($validator->errors()->toArray()), $request, [
                'errors' => $validator->errors()->toArray(),
                'email' => $request->input('email'),
                'ip' => get_client_ip()
            ]);

            return back()->withErrors($validator)->withInput($request->except('password', 'password_confirmation'));
        }

        $validated = $validator->validated();

        // Sanitize inputs
        $validated['name'] = trim($validated['name']);
        $validated['email'] = strtolower(trim($validated['email']));
        $validated['country'] = trim($validated['country']);
        if (isset($validated['phone']) && isset($validated['country_code'])) {
            // Combine country code and phone, ensure phone starts with '+'
            $validated['phone'] = trim($validated['country_code'] . preg_replace('/^\+/', '', trim($validated['phone'])));
        }

        // Email verification service check
        try {
            $emailVerificationResult = $this->emailVerifier->verify($validated['email']);

            if (!$emailVerificationResult['valid']) {
                $this->logAuthEvent('email_verification_failed', self::ERROR_CATEGORIES['EMAIL_VERIFICATION_FAILED'], $request, [
                    'email' => $validated['email'],
                    'service_response' => $emailVerificationResult,
                    'ip' => get_client_ip()
                ]);

                return back()->withErrors([
                    'auth_error' => $emailVerificationResult['message'] ?? 'Please provide a valid email address. This email appears to be invalid or disposable.',
                ])->withInput($request->except('password', 'password_confirmation'));
            }
        } catch (\Exception $e) {
            $this->logAuthEvent('email_service_error', self::ERROR_CATEGORIES['SYSTEM'], $request, [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
                'ip' => get_client_ip()
            ]);

            // Continue with signup even if email service fails (don't block user)
            Log::warning('Email verification service unavailable, proceeding with signup', [
                'email' => $validated['email'],
                'error' => $e->getMessage()
            ]);
        }

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'country' => $validated['country'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
                'registration_ip' => get_client_ip(),
                'last_login_ip' => get_client_ip(),
                'last_login_at' => now(),
            ]);

            // Clear rate limit on success (only increment on actual success)
            RateLimiter::clear($rateLimitKey);

            $this->logAuthEvent('successful_registration', 'success', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
                'country' => $user->country,
                'has_phone' => !empty($user->phone)
            ]);

            event(new Registered($user));
            Auth::login($user);

            // Check if user is admin and redirect appropriately
            if ($user->is_admin || $user->is_superuser) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('dashboard.level-selection');

        } catch (\Exception $e) {
            // Only increment rate limiter on actual database errors (not validation errors)
            RateLimiter::hit($rateLimitKey, $decaySeconds);

            $errorType = $this->determineDatabaseErrorType($e);

            $this->logAuthEvent('registration_database_error', $errorType, $request, [
                'email' => $validated['email'] ?? null,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'ip' => get_client_ip()
            ]);

            // Handle specific database errors
            if (str_contains($e->getMessage(), 'users_phone_unique')) {
                return back()->withErrors([
                    'phone' => 'This phone number is already registered. Please <a href="' . route('login') . '" class="text-primary-blue hover:underline">login instead</a> or use a different number.'
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            if (str_contains($e->getMessage(), 'users_email_unique')) {
                return back()->withErrors([
                    'email' => 'An account with this email already exists. Please <a href="' . route('login') . '" class="text-primary-blue hover:underline">login instead</a>.'
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            return back()->withErrors([
                'auth_error' => 'Registration failed due to a system error. Please try again or contact support if the problem persists.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }
    }

    public function logout(Request $request)
    {
        if ($request->user()->google_id) {
            try {
                Http::post('https://oauth2.googleapis.com/revoke', [
                    'token' => $request->user()->google_token,
                ]);
            } catch (\Exception $e) {
                Log::error('Google token revocation failed', [
                    'user_id' => $request->user()->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        $user = Auth::user();
        
        $this->logAuthEvent('user_logout', 'info', $request, [
            'user_id' => $user ? $user->id : null,
            'email' => $user ? $user->email : null
        ]);

        // Log user activity
        if ($user) {
            UserActivityService::logActivity(
                'user_logout',
                'User logged out',
                [
                    'logout_method' => 'manual',
                    'session_duration' => $user->last_login_at ? now()->diffInMinutes($user->last_login_at) : null
                ],
                $user->id,
                get_client_ip(),
                $request->userAgent()
            );
        }

        // Fire logout event
        if ($user) {
            event(new Logout('web', $user));
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home');
    }

    // Forgot Password & Reset Methods

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = strtolower(trim($request->email));

        $user = User::where('email', $email)->first();

        // Always return generic success message to prevent user enumeration
        $statusMessage = 'If an account exists for this email, you will receive password reset instructions.';

        if (!$user) {
            // Log for security monitoring (optional, careful not to log simple typos as attacks)
            Log::info('Password reset requested for non-existent email', ['email' => $email, 'ip' => $request->ip()]);
            return back()->with('status', $statusMessage);
        }

        // Check if user is Google Auth only (assuming google_id exists and password might be null or user is known to use Google)
        // Adjust logic if you allow both. For this requirement: "If Social Login... send email informing them"
        if ($user->google_id && empty($user->password)) { 
            // Send Google Account Info Mail
            Mail::to($user->email)->send(new GoogleAccountInfoMail($user));
            Log::info('Sent Google account info mail for password reset', ['email' => $email]);
            return back()->with('status', $statusMessage);
        }

        // Local Account Flow
        // 1. Generate secure random token
        $token = Str::random(64);
        
        // 2. Hash token for storage
        // Laravel's password_reset_tokens table usually has email, token, created_at.
        // We will store the HASHED token.
        // Note: verify your table structure. Default Laravel stores unhashed or hashed depending on provider.
        // We will manually insert to be sure.
        
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token), // Using BCrypt/Argon2 hashing for the token
                'created_at' => Carbon::now()
            ]
        );

        // 3. Send email with RAW token
        try {
            Mail::to($user->email)->send(new ResetPasswordMail($token, $email));
            Log::info('Sent password reset link', ['email' => $email]);
            return back()->with('status', $statusMessage);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Unable to send password reset link due to a temporary system error. Please try again later.');
        }
    }

    public function showResetPassword($token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => request()->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                PasswordRule::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(3)
            ],
        ]);

        $email = strtolower(trim($request->email));
        
        // Retrieve record
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return back()->withErrors(['email' => 'Invalid or expired password reset link.']);
        }

        // Check expiration (e.g., 60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return back()->withErrors(['email' => 'This password reset link has expired.']);
        }

        // Verify Token
        if (!Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Invalid password reset link.']);
        }

        // Update Password
        $user = User::where('email', $email)->first();
        if (!$user) {
             return back()->withErrors(['email' => 'User not found.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // Invalidate token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Send confirmation email
        Mail::to($user->email)->send(new PasswordChangedMail($user));

        // Log
        Log::info('Password reset successful', ['user_id' => $user->id, 'ip' => $request->ip()]);

        return redirect()->route('login')->with('status', 'Your password has been reset!');
    }

    /**
     * Generate throttle key for rate limiting
     */
    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email', '')) . '|' . get_client_ip();
    }

    /**
     * Enhanced logging for authentication events
     */
    protected function logAuthEvent(string $event, string $category, Request $request, array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'category' => $category,
            'ip' => get_client_ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'timestamp' => Carbon::now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ], $context);

        // Log to security channel for security-related events
        if (in_array($category, [self::ERROR_CATEGORIES['RATE_LIMIT'],
                                 self::ERROR_CATEGORIES['ACCOUNT_LOCKED'],
                                 self::ERROR_CATEGORIES['INVALID_CREDENTIALS']])) {
            Log::channel('security')->info($event, $logData);
        }

        // Log to auth channel for authentication events
        Log::channel('auth')->info($event, $logData);

        // Also log to daily for general tracking
        Log::info("Auth Event: {$event}", $logData);
    }

    /**
     * Determine error type from validation errors
     */
    protected function determineErrorType(array $errors): string
    {
        if (isset($errors['email']) && in_array('unique', $errors['email'])) {
            return self::ERROR_CATEGORIES['EMAIL_EXISTS'];
        }

        if (isset($errors['phone']) && in_array('unique', $errors['phone'])) {
            return self::ERROR_CATEGORIES['INVALID_PHONE'];
        }

        if (isset($errors['password'])) {
            return self::ERROR_CATEGORIES['PASSWORD_VALIDATION'];
        }

        if (isset($errors['email'])) {
            return self::ERROR_CATEGORIES['INVALID_EMAIL'];
        }

        return self::ERROR_CATEGORIES['VALIDATION'];
    }

    /**
     * Determine database error type
     */
    protected function determineDatabaseErrorType(\Exception $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'users_email_unique')) {
            return self::ERROR_CATEGORIES['EMAIL_EXISTS'];
        }

        if (str_contains($message, 'users_phone_unique')) {
            return self::ERROR_CATEGORIES['INVALID_PHONE'];
        }

        if (str_contains($message, 'Integrity constraint violation')) {
            return 'database_constraint';
        }

        return self::ERROR_CATEGORIES['SYSTEM'];
    }

    /**
     * Ensure user progress is initialized for the selected level group
     */
    private function ensureUserProgressInitializedForLevelGroup($user, $levelGroup)
    {
        try {
            // Check if user already has progress for this level group
            $existingProgress = UserProgress::where('user_id', $user->id)
                ->where('level_group', $levelGroup)
                ->first();

            if (!$existingProgress) {
                // Get grade levels for this level group
                $gradeLevels = $this->getGradeLevelsForLevelGroup($levelGroup);

                // Get actual counts from database for this level group
                $totalLessons = Video::approved()
                    ->whereIn('grade_level', $gradeLevels)
                    ->count();

                $totalQuizzes = Quiz::whereIn('grade_level', $gradeLevels)
                    ->count();

                // Create progress record for the selected level group
                UserProgress::create([
                    'user_id' => $user->id,
                    'current_level' => $levelGroup,
                    'level_group' => $levelGroup,
                    'total_lessons_in_level' => $totalLessons,
                    'completed_lessons' => 0,
                    'total_quizzes_in_level' => $totalQuizzes,
                    'completed_quizzes' => 0,
                    'average_quiz_score' => 0,
                    'completion_percentage' => 0,
                    'level_started_at' => now(),
                ]);

                Log::info('Initialized user progress for level group', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'level_group' => $levelGroup,
                    'total_lessons' => $totalLessons,
                    'total_quizzes' => $totalQuizzes
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to initialize user progress for level group', [
                'user_id' => $user->id,
                'level_group' => $levelGroup,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get grade levels for a level group
     */
    private function getGradeLevelsForLevelGroup($levelGroup)
    {
        $levelMappings = [
            'primary-lower' => ['Primary 1', 'Primary 2', 'Primary 3'],
            'primary-upper' => ['Primary 4', 'Primary 5', 'Primary 6'],
            'jhs' => ['JHS 1', 'JHS 2', 'JHS 3'],
            'shs' => ['SHS 1', 'SHS 2', 'SHS 3'],
            'university' => [], // University handled differently
        ];

        return $levelMappings[$levelGroup] ?? [];
    }
}
