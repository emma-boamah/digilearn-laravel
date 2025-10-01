<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Rules\Recaptcha;
use Illuminate\Support\Facades\URL;


class GoogleController extends Controller
{
    public function boot()
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
    // Add rate limiting to redirect
    public function redirectToGoogle(Request $request)
    {
        $state = Str::random(40);
        
        // Store state in session with proper persistence
        $request->session()->put('oauth_state', $state);
        $request->session()->save();
        
        if (config('services.google.recaptcha_enabled')) {
            $validator = Validator::make($request->all(), [
                'g-recaptcha-response' => ['required', new Recaptcha]
            ]);
            
            if ($validator->fails()) {
                return redirect()->route('login')
                    ->withErrors(['captcha' => 'Invalid reCAPTCHA. Please try again.']);
            }
        }
        
        return Socialite::driver('google')
            ->redirectUrl(route('auth.google.callback'))
            ->with(['state' => $state])
            ->redirect(); // Removed scopes() unless specifically needed
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            // Get session state without removing it
            $sessionState = $request->session()->get('oauth_state');
            
            if (!$sessionState || $request->state !== $sessionState) {
                Log::warning('Google OAuth state mismatch', [
                    'session_state' => $sessionState,
                    'request_state' => $request->state,
                    'session_id' => session()->getId(),
                    'ip' => request()->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                return redirect()->route('login')
                    ->withErrors(['error' => 'Invalid authentication state.']);
            }
            
            // Clear state after verification
            $request->session()->forget('oauth_state');
            
            // Use stateless() because we already validate state ourselves
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('auth.google.callback'))
                ->stateless()
                ->user();
            
            if (!$googleUser->getEmail() || !$googleUser->getId()) {
                throw new \Exception('Incomplete user data from Google');
            }
            
            $user = $this->findOrCreateUser($googleUser, request()->ip());
            
            Auth::login($user, true);
            
            // Regenerate session ID after login
            $request->session()->regenerate();
            
            // Check if user is admin and redirect appropriately
            $user = Auth::user();
            if ($user && ($user->is_admin || $user->is_superuser)) {
                // For admin users, check if they were trying to access admin panel
                $intendedUrl = session('url.intended');
                if ($intendedUrl && str_contains($intendedUrl, '/admin')) {
                    return redirect()->intended(route('admin.dashboard'));
                }
                // If no admin-specific intended URL, go to admin dashboard
                return redirect()->route('admin.dashboard');
            }
            
            // For regular users, use intended or default to main dashboard
            return redirect()->intended(route('dashboard.main'));

        } catch (\Exception $e) {
            Log::error('Google Auth Error: ' . ($e->getMessage() ?: get_class($e)), [
                'exception' => $e->getTraceAsString(),
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
                'params' => $request->all(),
                'session' => session()->all(),
                'ip' => request()->ip(),
                'headers' => $request->headers->all()
            ]);
            
            return redirect()->route('login')->withErrors([
                'error' => 'Authentication failed. Please try again or use another method.'
            ]);
        }
    }
    protected function findOrCreateUser($googleUser, $ip)
    {
        return User::withoutEvents(function () use ($googleUser, $ip) {
            // Check if a user exists with this email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Update existing user with Google ID
                $updateData = [
                    'google_id' => $googleUser->getId(),
                    'last_login_ip' => $ip,
                    'last_login_at' => now()
                ];

                // Update name/avatar if missing
                if (!$user->name) $updateData['name'] = $googleUser->getName();
                if (!$user->avatar) $updateData['avatar'] = $googleUser->getAvatar();
                
                $user->update($updateData);
                return $user;
            }

            // Check for existing Google ID
            $user = User::where('google_id', $googleUser->getId())->first();

            if ($user) {
                // Update Google user profile
                $user->update([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                    'last_login_ip' => $ip,
                    'last_login_at' => now(),
                ]);
                return $user;
            }

            // Create new user
            return User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
                'password' => bcrypt(Str::random(64)),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
                'registration_ip' => $ip,
                'last_login_ip' => $ip,
                'last_login_at' => now(),
                'google_metadata' => [
                    'locale' => $googleUser->user['locale'] ?? null,
                    'verified' => $googleUser->user['email_verified'] ?? false,
                ]
            ]);
        });
    }

}
