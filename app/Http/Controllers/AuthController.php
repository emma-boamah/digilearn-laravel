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
use Illuminate\Support\Facades\Http;

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
                    'ip' => get_client_ip()
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
            if (session('selected_level')) {
                return redirect()->route('dashboard.main');
            } else {
                return redirect()->route('dashboard.level-selection');
            }
        }

        // Check for too many failed attempts
        $key = $this->throttleKey($request);
        if (RateLimiter::tooManyAttempts($key, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            $this->logAuthEvent('login_rate_limit_exceeded', self::ERROR_CATEGORIES['RATE_LIMIT'], $request, [
                'ip' => get_client_ip(),
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
                'ip' => get_client_ip(),
                'email' => $request->input('email'),
                'lockout_seconds' => $seconds,
                'attempts' => RateLimiter::attempts($key)
            ]);

            return back()->withErrors([
                'rate_limit' => "Too many login attempts. Please try again in " . ceil($seconds / 60) . " minutes.",
            ])->withInput($request->except('password'));
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255'
            ],
        ], [
            'email.email' => 'Please enter a valid email address.',
            'email.regex' => 'The email format is invalid.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        if ($validator->fails()) {
            RateLimiter::hit($key, self::LOCKOUT_DURATION * 60);

            $this->logAuthEvent('validation_failed', self::ERROR_CATEGORIES['VALIDATION'], $request, [
                'errors' => $validator->errors()->toArray(),
                'email' => $request->input('email'),
                'ip' => get_client_ip()
            ]);

            return back()->withErrors($validator)->withInput($request->except('password'));
        }

        $credentials = $validator->validated();
        $credentials['email'] = strtolower(trim($credentials['email']));

        // Check if user exists
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            RateLimiter::hit($key, self::LOCKOUT_DURATION * 60);

            $this->logAuthEvent('user_not_found', self::ERROR_CATEGORIES['INVALID_CREDENTIALS'], $request, [
                'email' => $credentials['email'],
                'ip' => get_client_ip()
            ]);

            return back()->withErrors([
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

            return back()->withErrors([
                'email' => 'Your account has been temporarily locked due to too many failed attempts. Please try again later or reset your password.',
            ])->withInput($request->except('password'));
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
                'last_login_ip' => get_client_ip(),
            ]);

            $this->logAuthEvent('successful_login', 'success', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
                'remember_me' => $request->boolean('remember')
            ]);

            event(new Login('web', $user, false));

            // Check if user is admin and redirect appropriately
            if ($user->is_admin || $user->is_superuser) {
                $intendedUrl = session('url.intended');
                if ($intendedUrl && str_contains($intendedUrl, '/admin')) {
                    return redirect()->intended(route('admin.dashboard'));
                }
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('dashboard.level-selection');
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

        return back()->withErrors([
            'password' => 'The password you entered is incorrect.',
        ])->withInput($request->except('password'));
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
            if (session('selected_level')) {
                return redirect()->route('dashboard.main');
            } else {
                return redirect()->route('dashboard.level-selection');
            }
        }
        return view('auth.signup');
    }

    
    
    public function signup(Request $request)
    {
        // Rate limit by email only (safe for public/shared IPs) - check before validation
        $rateLimitKey = 'signup:' . get_client_ip() . '|' . strtolower($request->input('email', ''));

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
                'rate_limit' => "Too many signup attempts. Please try again in " . ceil($seconds / 60) . " minutes.",
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        // (Optional) Validate CAPTCHA here if you add one

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
                'unique:users',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
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
                    ->uncompromised(3) // Check against 3 breaches
            ],
        ], [
            'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.unique' => 'An account with this email already exists. Please login or use a different email.',
            'email.regex' => 'Please enter a valid email address.',
            'phone.unique' => 'This phone number is already registered. Please use a different number or login instead.',
            'phone.regex' => 'Please enter a valid phone number.',
            'country.regex' => 'Country name can only contain letters, spaces, and hyphens.',
            'country_code.regex' => 'Please select a valid country code.',
            'password.uncompromised' => 'This password has been found in data breaches. Please choose a more secure password.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            RateLimiter::hit($rateLimitKey, $decaySeconds);

            // Extract specific errors for logging
            $errors = $validator->errors()->toArray();
            $errorType = $this->determineErrorType($errors);

            $this->logAuthEvent('signup_validation_failed', $errorType, $request, [
                'errors' => $errors,
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
                    'email' => $emailVerificationResult['message'] ?? 'Please provide a valid email address. This email appears to be invalid or disposable.',
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
                'last_login_ip' => get_client_ip(), // Use helper function to get client IP
                'last_login_at' => now(),
            ]);

            // Clear rate limit on success
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
            // Increment rate limiter on error
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
                    'phone' => 'This phone number is already registered. Please use a different number or login instead.'
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            if (str_contains($e->getMessage(), 'users_email_unique')) {
                return back()->withErrors([
                    'email' => 'An account with this email already exists. Please login instead.'
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            return back()->withErrors([
                'system' => 'Registration failed due to a system error. Please try again or contact support if the problem persists.',
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

        // Fire logout event
        if ($user) {
            event(new Logout('web', $user));
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home');
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