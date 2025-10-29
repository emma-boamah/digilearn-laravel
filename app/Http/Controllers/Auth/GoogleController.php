<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\UserProgress;
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

            // For regular users, check if they have selected a level group
            if (!session('selected_level_group')) {
                return redirect()->route('dashboard.level-selection');
            }

            // Ensure user progress is initialized for their selected level group
            $this->ensureUserProgressInitializedForLevelGroup($user, session('selected_level_group'));

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
                $totalLessons = \App\Models\Video::approved()
                    ->whereIn('grade_level', $gradeLevels)
                    ->count();

                $totalQuizzes = \App\Models\Quiz::whereIn('grade_level', $gradeLevels)
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
