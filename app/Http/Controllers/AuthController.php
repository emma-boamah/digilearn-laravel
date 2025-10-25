<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use App\Models\User;
use App\Models\WebsiteLockSetting;
use App\Models\SuperuserRecoveryCode;
use Carbon\Carbon;
use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    protected $emailVerifier;

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
            $this->logSecurityEvent('authenticated_user_accessed_login', $request);

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
            $this->logSecurityEvent('login_rate_limit_exceeded', $request, [
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
            
            $this->logSecurityEvent('blocked_login_attempt', $request, [
                'ip' => get_client_ip(),
                'email' => $request->input('email'),
                'lockout_seconds' => $seconds
            ]);
            
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in " . ceil($seconds / 60) . " minutes.",
            ])->withInput($request->except('password'));
        }

        // Validate input with enhanced rules
        $credentials = $request->validate([
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
        ]);

        // Sanitize email
        $credentials['email'] = strtolower(trim($credentials['email']));

        // Check if user exists and is active
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            RateLimiter::hit($key, self::LOCKOUT_DURATION * 60);
            
            $this->logSecurityEvent('login_attempt_nonexistent_user', $request, [
                'email' => $credentials['email'],
                'ip' => get_client_ip(), // Use helper function to get client IP
                'registration_ip' => get_client_ip(), // In signup
            ]);
            
            // Generic error message to prevent user enumeration
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->except('password'));
        }

        // Check if user account is locked
        if ($user->locked_until && Carbon::now()->lt($user->locked_until)) {
            $this->logSecurityEvent('login_attempt_locked_account', $request, [
                'user_id' => $user->id,
                'email' => $credentials['email'],
                'locked_until' => $user->locked_until
            ]);
            
            return back()->withErrors([
                'email' => 'Your account has been temporarily locked. Please try again later.',
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
                'registration_ip' => get_client_ip(), // From signup
            ]);

            // Log successful login
            $this->logSecurityEvent('successful_login', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
                'remember_me' => $request->boolean('remember')
            ]);

            // Fire login event
            event(new Login('web', $user, false));

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

            // For regular users, go to dashboard
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

        $this->logSecurityEvent('failed_login_attempt', $request, [
            'user_id' => $user->id,
            'email' => $credentials['email'],
            'failed_attempts' => $failedAttempts,
            'ip' => get_client_ip()
        ]);

        // Fire failed login event
        event(new Failed('web', $user, $credentials));

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
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
        // Validating email first to avoid empty email rate limiting
        $request->validate([
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
        ]);
        // Rate limit by email only (safe for public/shared IPs)
        $rateLimitKey = 'signup:' . get_client_ip() . '|' . strtolower($request->input('email', ''));

        $maxAttempts = 5;
        $decaySeconds = 600; // 10 minutes

        // Check rate limiting before validation
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors([
                'email' => "Too many signup attempts. Please try again in " . ceil($seconds / 60) . " minutes.",
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        // (Optional) Validate CAPTCHA here if you add one

        // Validate input
        $validated = $request->validate([
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
                    ->uncompromised()
            ],
        ], [
            'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.unique' => 'An account with this email already exists.',
            'email.regex' => 'Please enter a valid email address.',
            'phone.unique' => 'This phone number is already registered.',
            'phone.regex' => 'Please enter a valid phone number.',
            'country.regex' => 'Country name can only contain letters, spaces, and hyphens.',
            'country_code.regex' => 'Please select a valid country code.',
            'password.uncompromised' => 'The given password has appeared in a data breach. Please choose a different password.',
        ]);

        // Sanitize inputs
        $validated['name'] = trim($validated['name']);
        $validated['email'] = strtolower(trim($validated['email']));
        $validated['country'] = trim($validated['country']);
        if (isset($validated['phone']) && isset($validated['country_code'])) {
            // Combine country code and phone, ensure phone starts with '+'
            $validated['phone'] = trim($validated['country_code'] . preg_replace('/^\+/', '', trim($validated['phone'])));
        }

        if (!$this->emailVerifier->verify($validated['email'])) {
            return back()->withErrors([
                'email' => 'Please provide a valid email address. This email appears to be invalid or disposable.',
            ])->withInput($request->except('password', 'password_confirmation'));
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

            $this->logSecurityEvent('successful_registration', $request, [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            event(new Registered($user));
            Auth::login($user);

            // Check if user is admin and redirect appropriately
            if ($user->is_admin || $user->is_superuser) {
                return redirect()->route('admin.dashboard');
            }

            // For regular users, go to level selection
            return redirect()->route('dashboard.level-selection');

        } catch (\Exception $e) {
            // Increment rate limiter on error
            RateLimiter::hit($rateLimitKey, $decaySeconds);

            $this->logSecurityEvent('registration_error', $request, [
                'email' => $validated['email'] ?? null,
                'error' => $e->getMessage()
            ]);

            // Handle duplicate phone number specifically
            if (str_contains($e->getMessage(), 'users_phone_unique')) {
                return back()->withErrors([
                    'phone' => 'This phone number is already registered. Please use a different number or skip phone verification.'
                ])->withInput($request->except('password', 'password_confirmation'));
            }

            return back()->withErrors([
                'email' => 'Registration failed. Please try again.',
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
        
        $this->logSecurityEvent('user_logout', $request, [
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
     * Log security events
     */
    protected function logSecurityEvent(string $event, Request $request, array $context = []): void
    {
        Log::channel('security')->info($event, array_merge([
            'ip' => get_client_ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'timestamp' => Carbon::now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ], $context));
    }
}