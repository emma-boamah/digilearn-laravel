<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Maximum login attempts before lockout
     */
    const MAX_LOGIN_ATTEMPTS = 5;
    
    /**
     * Lockout duration in minutes
     */
    const LOCKOUT_DURATION = 15;

    public function showLogin(Request $request)
    {
        // Check if user is already authenticated
        if (Auth::check()) {
            $this->logSecurityEvent('authenticated_user_accessed_login', $request);
            
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
            
            $this->logSecurityEvent('blocked_login_attempt', $request, [
                'ip' => $request->ip(),
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
                'ip' => $request->ip()
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
                'last_login_ip' => $request->ip()
            ]);

            // Log successful login
            $this->logSecurityEvent('successful_login', $request, [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // Fire login event
            event(new Login('web', $user, false));

            // Check if email is verified (if email verification is enabled)
            if (!$user->hasVerifiedEmail() && config('auth.email_verification_required', false)) {
                Auth::logout();
                return redirect()->route('verification.notice');
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
            'locked_until' => $lockUntil
        ]);

        $this->logSecurityEvent('failed_login_attempt', $request, [
            'user_id' => $user->id,
            'email' => $credentials['email'],
            'failed_attempts' => $failedAttempts,
            'ip' => $request->ip()
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
        // Rate limiting for signup
        $key = 'signup:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many signup attempts. Please try again in " . ceil($seconds / 60) . " minutes.",
            ]);
        }

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
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ], [
            'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.regex' => 'Please enter a valid email address.',
            'country.regex' => 'Country name can only contain letters, spaces, and hyphens.',
            'password.uncompromised' => 'The given password has appeared in a data breach. Please choose a different password.',
        ]);

        // Sanitize inputs
        $validated['name'] = trim($validated['name']);
        $validated['email'] = strtolower(trim($validated['email']));
        $validated['country'] = trim($validated['country']);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'country' => $validated['country'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => config('auth.email_verification_required', false) ? null : now(),
                'registration_ip' => $request->ip(),
                'last_login_ip' => $request->ip(),
                'last_login_at' => Carbon::now(),
            ]);

            // Clear signup rate limiting on success
            RateLimiter::clear($key);

            // Log successful registration
            $this->logSecurityEvent('successful_registration', $request, [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // Fire registered event
            event(new Registered($user));

            Auth::login($user);

            // Send email verification if required
            if (config('auth.email_verification_required', false)) {
                $user->sendEmailVerificationNotification();
                return redirect()->route('verification.notice');
            }

            return redirect()->route('dashboard.level-selection');

        } catch (\Exception $e) {
            RateLimiter::hit($key, 300); // 5 minutes lockout on error
            
            $this->logSecurityEvent('registration_error', $request, [
                'email' => $validated['email'],
                'error' => $e->getMessage()
            ]);

            return back()->withErrors([
                'email' => 'Registration failed. Please try again.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }
    }

    public function logout(Request $request)
    {
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
        return Str::lower($request->input('email', '')) . '|' . $request->ip();
    }

    /**
     * Log security events
     */
    protected function logSecurityEvent(string $event, Request $request, array $context = []): void
    {
        Log::channel('security')->info($event, array_merge([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'timestamp' => Carbon::now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ], $context));
    }
}