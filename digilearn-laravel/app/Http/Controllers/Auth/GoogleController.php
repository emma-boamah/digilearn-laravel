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


class GoogleController extends Controller
{
    // Add rate limiting to redirect
    public function redirectToGoogle(Request $request)
    {
        // Verify state token to prevent CSRF
        $state = Str::random(40);
        $request->session()->put('oauth_state', $state);
        $request->session()->save(); 
        
        // Validate recaptcha if enabled
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
            ->with(['state' => $state])
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            // Verify state parameter
            if ($request->state !== $request->session()->pull('oauth_state')) {
                Log::warning('Google OAuth state mismatch', [
                    'ip' =>get_client_ip(),
                    'user_agent' => $request->userAgent()
                ]);
                return redirect()->route('login')
                    ->withErrors(['error' => 'Invalid authentication state.']);
            }

            // Check rate limiting
            $rateLimitKey = 'google-auth:'.get_client_ip();
            if (Cache::has($rateLimitKey)) {
                Log::alert('Google OAuth rate limit exceeded', [
                    'ip' => get_client_ip(),
                    'user_agent' => $request->userAgent()
                ]);
                return redirect()->route('login')
                    ->withErrors(['error' => 'Too many attempts. Please try again later.']);
            }

            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Validate essential fields
            if (!$googleUser->getEmail() || !$googleUser->getId()) {
                throw new \Exception('Incomplete user data from Google');
            }
            
            // Prevent account enumeration
            $user = $this->findOrCreateUser($googleUser, get_client_ip());
            
            // Log user in
            Auth::login($user, true);
            
            // Rotate session ID
            $request->session()->regenerate();
            
            return redirect()->intended(route('dashboard.main'));

        } catch (\Exception $e) {
            // Rate limit on errors
            Cache::put($rateLimitKey, true, now()->addMinutes(5));
            
            Log::error('Google Auth Error: ' . $e->getMessage(), [
                'exception' => $e,
                'ip' => get_client_ip(),
            ]);
            
            return redirect()->route('login')->withErrors([
                'error' => 'Authentication failed. Please try another method.'
            ]);
        }
    }

    protected function findOrCreateUser($googleUser, $ip)
    {
        return User::withoutEvents(function () use ($googleUser, $ip) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Preserve existing password when linking Google
                $updateData = [
                    'google_id' => $googleUser->getId(),
                    'last_login_ip' => $ip,
                    'last_login_at' => now()
                ];

                // Only update name/avatar if missing
                if (!$user->name) $updateData['name'] = $googleUser->getName();
                if (!$user->avatar) $updateData['avatar'] = $googleUser->getAvatar();

                $user->update($updateData);
                return $user;
            }

            // Create new user
            return User::create([
                'google_id' => $googleUser->getId(),
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'avatar' => $googleUser->getAvatar(),
                'password' => bcrypt(Str::random(64)), // new account, so generate password
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
