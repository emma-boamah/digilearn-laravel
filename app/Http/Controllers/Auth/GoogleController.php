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
    // Reuse error categories from AuthController
    const ERROR_CATEGORIES = [
        'INVALID_STATE' => 'invalid_state',
        'NO_EMAIL' => 'no_email',
        'GOOGLE_AUTH_FAILED' => 'google_auth_failed',
        'USER_CREATION_FAILED' => 'user_creation_failed',
        'SYSTEM' => 'system'
    ];

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
                $this->logGoogleAuthEvent('invalid_state', self::ERROR_CATEGORIES['INVALID_STATE'], $request, [
                    'session_state' => $sessionState,
                    'request_state' => $request->state,
                    'ip' => $request->ip()
                ]);

                return redirect()->route('login')
                    ->withErrors(['error' => 'Security validation failed. Please try logging in again.']);
            }

            $request->session()->forget('oauth_state');

            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('auth.google.callback'))
                ->stateless()
                ->user();

            if (!$googleUser->getEmail()) {
                $this->logGoogleAuthEvent('no_email_provided', self::ERROR_CATEGORIES['NO_EMAIL'], $request, [
                    'google_user_id' => $googleUser->getId(),
                    'google_name' => $googleUser->getName(),
                    'ip' => $request->ip()
                ]);

                return redirect()->route('login')->withErrors([
                    'error' => 'Your Google account doesn\'t provide an email address. Please sign up with email instead.'
                ]);
            }

            // 🔹 Hybrid signup flow
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                try {
                    // Auto create new user
                    $user = User::create([
                        'name' => $googleUser->getName() ?: 'Google User',
                        'email' => $googleUser->getEmail(),
                        'avatar' => $googleUser->getAvatar(),
                        'password' => bcrypt(Str::random(64)),
                        'google_id' => $googleUser->getId(),
                        'email_verified_at' => now(),
                        'registration_ip' => $request->ip(),
                        'last_login_ip' => $request->ip(),
                        'last_login_at' => now(),
                    ]);

                    $this->logGoogleAuthEvent('new_user_created', 'success', $request, [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'via_google' => true
                    ]);

                } catch (\Exception $e) {
                    $this->logGoogleAuthEvent('user_creation_failed', self::ERROR_CATEGORIES['USER_CREATION_FAILED'], $request, [
                        'email' => $googleUser->getEmail(),
                        'error' => $e->getMessage(),
                        'error_code' => $e->getCode(),
                        'ip' => $request->ip()
                    ]);

                    return redirect()->route('login')->withErrors([
                        'error' => 'Failed to create account. This email might already be registered. Please try logging in with email instead.'
                    ]);
                }
            } else {
                // Update existing user info if missing
                $updateData = [
                    'last_login_ip' => $request->ip(),
                    'last_login_at' => now(),
                ];

                // Only update google_id if not already set
                if (!$user->google_id) {
                    $updateData['google_id'] = $googleUser->getId();
                }

                $user->update($updateData);

                $this->logGoogleAuthEvent('existing_user_login', 'success', $request, [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'had_google_id' => !empty($user->google_id),
                    'via_google' => true
                ]);
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            $this->logGoogleAuthEvent('google_login_successful', 'success', $request, [
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

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->logGoogleAuthEvent('google_service_unavailable', self::ERROR_CATEGORIES['GOOGLE_AUTH_FAILED'], $request, [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->route('login')->withErrors([
                'error' => 'Google authentication service is temporarily unavailable. Please try again later or sign in with email.'
            ]);

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            $this->logGoogleAuthEvent('invalid_state_exception', self::ERROR_CATEGORIES['INVALID_STATE'], $request, [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            return redirect()->route('login')->withErrors([
                'error' => 'Your session has expired. Please try logging in again.'
            ]);

        } catch (\Exception $e) {
            $this->logGoogleAuthEvent('unexpected_error', self::ERROR_CATEGORIES['SYSTEM'], $request, [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'error_code' => $e->getCode(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')->withErrors([
                'error' => 'Google authentication failed. Please try again or sign in with email.'
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

    /**
     * Enhanced logging for Google auth events
     */
    private function logGoogleAuthEvent(string $event, string $category, Request $request, array $context = []): void
    {
        $logData = array_merge([
            'event' => $event,
            'category' => $category,
            'service' => 'google_auth',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
            'session_id' => $request->session()->getId(),
        ], $context);

        // Log to appropriate channels
        if ($category === 'success') {
            Log::channel('auth')->info($event, $logData);
        } else {
            Log::channel('security')->warning($event, $logData);
        }

        // Always log to daily for tracking
        Log::info("Google Auth: {$event}", $logData);
    }
}
