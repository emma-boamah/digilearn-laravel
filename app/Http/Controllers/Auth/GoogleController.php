<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class GoogleController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        $state = Str::random(40);
        $request->session()->put('oauth_state', $state);
        $request->session()->save();

        return Socialite::driver('google')
            ->redirectUrl(route('auth.google.callback'))
            ->with(['state' => $state])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $sessionState = $request->session()->get('oauth_state');

            if (!$sessionState || $request->state !== $sessionState) {
                return redirect()->route('login')
                    ->withErrors(['error' => 'Invalid authentication state.']);
            }

            $request->session()->forget('oauth_state');

            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('auth.google.callback'))
                ->stateless()
                ->user();

            if (!$googleUser->getEmail()) {
                throw new \Exception('Google account does not provide an email');
            }

            // 🔹 Hybrid signup flow
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Auto create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt(Str::random(64)),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                    'registration_ip' => $request->ip(),
                    'last_login_ip' => $request->ip(),
                    'last_login_at' => now(),
                ]);
            } else {
                // Update existing user info if missing
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'last_login_ip' => $request->ip(),
                    'last_login_at' => now(),
                ]);
            }

            Auth::login($user, true); // Always remember Google users
            $request->session()->regenerate();

            Log::info('Google login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'has_remember_token' => !empty($user->remember_token),
                'session_id' => $request->session()->getId()
            ]);

            // 🔹 Role-based redirect
            if ($user->is_admin || $user->is_superuser) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('dashboard.main'));

        } catch (\Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return redirect()->route('login')->withErrors([
                'error' => 'Google authentication failed. Please try again.'
            ]);
        }
    }
}
