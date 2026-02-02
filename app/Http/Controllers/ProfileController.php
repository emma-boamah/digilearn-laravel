<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Models\PricingPlan;
use App\Models\UserSubscription; // Added for subscription methods
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\UserPreference;
use App\Services\UserActivityService;
use App\Services\SubscriptionAccessService;

class ProfileController extends Controller
{
    /**
     * Show the profile page
     */
    public function show()
    {
        $user = Auth::user();

        // Load user with current subscription
        $user->load(['currentSubscription.pricingPlan']);

        // Mask phone number for display
        $maskedPhone = $user->phone ? $this->maskPhoneNumber($user->phone) : null;

        // Get all available pricing plans for potential upgrades
        $availablePlans = PricingPlan::active()->ordered()->get();

        // Get user's subject preferences for display
        $userSubjectPreferences = UserPreference::getSubjectPreferences($user->id)->pluck('preference_value')->toArray();

        // Get user's grade notification opt-outs
        $gradeOptOuts = UserPreference::where('user_id', $user->id)
            ->where('preference_type', 'opt_out_grade_notification')
            ->pluck('preference_value')
            ->toArray();

        // Get grade levels the user has access to based on their subscription
        $allGradeLevels = SubscriptionAccessService::getAllowedGradeLevels($user);

        return view('dashboard.profile', compact('user', 'maskedPhone', 'availablePlans', 'userSubjectPreferences', 'gradeOptOuts', 'allGradeLevels'));
    }

    /**
     * Show the settings page.
     */
    public function settings()
    {
        $user = Auth::user();

        // Get user's grade notification opt-outs
        $gradeOptOuts = UserPreference::where('user_id', $user->id)
            ->where('preference_type', 'opt_out_grade_notification')
            ->pluck('preference_value')
            ->toArray();

        // Get grade levels the user has access to based on their subscription
        $allGradeLevels = SubscriptionAccessService::getAllowedGradeLevels($user);

        return view('dashboard.settings', compact('user', 'gradeOptOuts', 'allGradeLevels'));
    }

    /**
     * Show the user profile page.
     */
    public function showProfile()
    {
        $user = Auth::user();
        $currentSubscription = $user->currentSubscription;
        $pricingPlans = PricingPlan::all();

        // Prepare subscription info for the view
        $subscriptionInfo = null;
        if ($currentSubscription) {
            $subscriptionInfo = [
                'plan_name' => $currentSubscription->pricingPlan->name,
                'status' => $currentSubscription->status,
                'start_date' => $currentSubscription->start_date->format('M d, Y'),
                'end_date' => $currentSubscription->end_date ? $currentSubscription->end_date->format('M d, Y') : 'N/A',
                'days_remaining' => $currentSubscription->days_remaining,
                'trial_days_remaining' => $currentSubscription->trial_days_remaining,
                'is_trial' => $currentSubscription->isInTrial(),
                'is_active' => $currentSubscription->isActive(),
                'is_cancelled' => $currentSubscription->isCancelled(),
            ];
        }

        Log::channel('security')->info('profile_page_accessed', [
            'user_id' => Auth::id(),
            'subscription_plan' => $currentSubscription?->pricingPlan?->name ?? 'Free',
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return view('dashboard.profile', compact('user', 'subscriptionInfo', 'pricingPlans'));
    }

    /**
     * Update the user's profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255|min:2|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'last_name' => 'required|string|max:255|min:2|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'email' => 'required|email|unique:users,email,' . $user->id . '|max:255',
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\s\-()]+$/',
            'date_of_birth' => 'nullable|date|before:today',
            'country' => 'required|string|max:100|regex:/^[a-zA-Z\s\-]+$/',
            'city' => 'nullable|string|max:255|regex:/^[a-zA-Z\s\-\'\.]+$/',
            'education_level' => 'nullable|string|in:primary,jhs,shs,university',
            'grade' => 'nullable|string|max:10',
            'preferred_language' => 'required|string|max:5|in:en,tw,ga,ee,fr',
            'learning_style' => 'nullable|string|in:visual,auditory,kinesthetic,mixed',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:7000',
            'subjects' => 'nullable|array',
            'subjects.*' => 'string|in:mathematics,science,programming,english,history,geography',
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'phone.regex' => 'Please enter a valid phone number.',
            'country.regex' => 'Country name can only contain letters, spaces, and hyphens.',
            'city.regex' => 'City name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'subjects.*.in' => 'Invalid subject selected.',
        ]);

    // Check grade access
    if (isset($validated['grade']) && !empty($validated['grade'])) {
        $levelGroup = $this->getLevelGroupForGrade($validated['grade']);
        if ($levelGroup && !$this->hasAccessToLevelGroup($user, $levelGroup)) {
            return back()->withErrors(['grade' => 'You do not have access to this grade level. Please upgrade your subscription.']);
        }
    }

    try {
            $avatarUpdated = false;
            $avatarPath = null;

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');

                Log::info('avatar_upload_attempt', [
                    'user_id'   => $user->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'ip'        => $request->ip()
                ]);

                // Delete old avatar if it exists and is stored locally
                if ($user->avatar 
                    && !preg_match('/^https?:\/\//', $user->avatar) 
                    && Storage::disk('public')->exists($user->avatar)) 
                {
                    Storage::disk('public')->delete($user->avatar);
                    Log::info('old_avatar_deleted', [
                        'user_id'  => $user->id,
                        'old_path' => $user->avatar
                    ]);
                }

                // Generate unique filename and store the file
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('avatars', $filename, 'public');

                // Confirm file is stored
                if (!Storage::disk('public')->exists($path)) {
                    throw new \Exception('Avatar file was not stored successfully: ' . $path);
                }

                // Generate the full URL for storage
                $avatarUrl = Storage::url($path);

                // Save only the relative path (e.g. "avatars/xyz.jpg") in DB
                $user->avatar = $path;
                $avatarPath   = $path;
                $avatarUpdated = true;

                Log::info('avatar_file_stored', [
                    'user_id'    => $user->id,
                    'db_path'    => $path, // what’s saved in DB
                    'public_url' => Storage::url($path), // what you show in views
                ]);
            }


            // Combine first and last name
            $validated['name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);
            unset($validated['first_name'], $validated['last_name']);

            // Remove avatar from validated data since we handle it separately
            unset($validated['avatar']);

            // Sanitize inputs
            $validated['email'] = strtolower(trim($validated['email']));
            if (isset($validated['city'])) {
                $validated['city'] = trim($validated['city']);
            }
            if (isset($validated['bio'])) {
                $validated['bio'] = trim($validated['bio']);
            }

            // Handle subject preferences
            if (isset($validated['subjects'])) {
                // Delete existing subject preferences
                UserPreference::where('user_id', $user->id)
                    ->where('preference_type', 'subject')
                    ->delete();

                // Add new subject preferences
                foreach ($validated['subjects'] as $subject) {
                    UserPreference::create([
                        'user_id' => $user->id,
                        'preference_type' => 'subject',
                        'preference_value' => $subject,
                        'weight' => 1, // Default weight
                    ]);
                }
            }

            // Remove subjects from validated data since we handle it separately
            unset($validated['subjects']);

            // Update user
            $updateResult = $user->update($validated);

            // Verify avatar was saved to database if it was updated
            if ($avatarUpdated) {
                $user->refresh(); // Refresh the model from database
                if ($user->avatar !== $avatarPath) {
                    Log::error('avatar_save_failed', [
                        'user_id' => $user->id,
                        'expected_path' => $avatarPath,
                        'actual_path' => $user->avatar,
                        'update_result' => $updateResult
                    ]);
                    throw new \Exception('Avatar was not saved to database correctly');
                }

                Log::info('avatar_save_verified', [
                    'user_id' => $user->id,
                    'path' => $user->avatar
                ]);
            }

            // Log the profile update
            Log::info('profile_updated', [
                'user_id' => $user->id,
                'email' => $user->email,
                'avatar_updated' => $avatarUpdated,
                'avatar_path' => $user->avatar,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully!',
                    'avatar_url' => $user->avatar_url,
                    'avatar_updated' => $avatarUpdated,
                    'user' => $user->refresh()
                ]);
            }

            return redirect()->back()->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            // If avatar was uploaded but update failed, clean up the uploaded file
            if (isset($avatarPath) && Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
                Log::info('avatar_cleanup_after_error', [
                    'user_id' => $user->id,
                    'path' => $avatarPath
                ]);
            }

            Log::error('profile_update_error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'avatar_updated' => $avatarUpdated ?? false,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update profile. Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update profile. Please try again.');
        }
    }

    

    /**
     * Get the current user's avatar information
     *
     * 
     */
    public function getAvatarInfo(Request $request)
    {
        try {
            $user = $request->user();
            
            return response()->json([
                'success' => true,
                'avatar_url' => $user->avatar_url,
                'name' => $user->name,
                'initials' => $user->initials
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get avatar info: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load avatar information'
            ], 500);
        }
    }

    /**
     * Delete the user's account
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided password is incorrect.'
            ], 422);
        }

        try {
            // Delete avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Log account deletion
            Log::info('account_deleted', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            // Logout and delete user
            Auth::logout();
            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account deleted successfully!',
                    'redirect' => route('home')
                ]);
            }

            if ($user->google_id) {
                // Delete Google-related data
                $user->update([
                    'google_id' => null,
                    'google_metadata' => null,
                    'avatar' => null,
                ]);
            }

            return redirect()->route('home')->with('success', 'Account deleted successfully!');

        } catch (\Exception $e) {
            Log::error('account_deletion_error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete account. Please try again.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete account. Please try again.');
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string',
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
            'password.uncompromised' => 'The given password has appeared in a data breach. Please choose a different password.',
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The current password is incorrect.'
            ], 422);
        }

        try {
            $user->update([
                'password' => Hash::make($validated['password'])
            ]);

            // Log password change
            Log::info('password_changed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('password_update_error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update password. Please try again.'
            ], 500);
        }
    }

    /**
     * Update user phone number with verification
     */
    public function updatePhone(Request $request)
    {
        $user = Auth::user();

        // Rate limiting for phone updates
        $key = 'phone-update:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many phone update attempts. Please try again in " . ceil($seconds / 60) . " minutes."
            ], 429);
        }

        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-()]+$/',
                'unique:users,phone,' . $user->id
            ],
            'country_code' => [
                'required',
                'string',
                'max:5',
                'regex:/^\+[0-9]{1,4}$/'
            ],
            'current_password' => 'required|string'
        ], [
            'phone.unique' => 'This phone number is already registered to another account.',
            'phone.regex' => 'Please enter a valid phone number.',
            'country_code.regex' => 'Please select a valid country code.',
            'current_password.required' => 'Please enter your current password to confirm this change.',
        ]);

        // Verify current password for security
        if (!Hash::check($validated['current_password'], $user->password)) {
            RateLimiter::hit($key, 900); // 15 minute penalty
            
            Log::warning('phone_update_invalid_password', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'The provided password is incorrect.'
            ], 422);
        }

        try {
            $fullPhoneNumber = $validated['country_code'] . ' ' . trim($validated['phone']);
            
            // Update phone number (unverified initially)
            $user->phone = $fullPhoneNumber;
            $user->phone_verified_at = null;
            $user->save();

            // Clear rate limiting on success
            RateLimiter::clear($key);

            // Send verification SMS
            $this->sendPhoneVerification($user);

            // Log successful phone update
            Log::info('phone_number_updated', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone_masked' => $this->maskPhoneNumber($fullPhoneNumber),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phone number updated successfully! Please check your phone for a verification code.',
                'phone_masked' => $this->maskPhoneNumber($fullPhoneNumber)
            ]);

        } catch (\Exception $e) {
            RateLimiter::hit($key, 900);
            
            Log::error('phone_update_error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update phone number. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify phone number with SMS code
     */
    public function verifyPhone(Request $request)
    {
        $user = Auth::user();

        // Rate limiting for verification attempts
        $key = 'phone-verify:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many verification attempts. Please try again in " . ceil($seconds / 60) . " minutes."
            ], 429);
        }

        $validated = $request->validate([
            'verification_code' => [
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]{6}$/'
            ]
        ], [
            'verification_code.required' => 'Please enter the verification code.',
            'verification_code.size' => 'Verification code must be 6 digits.',
            'verification_code.regex' => 'Verification code must contain only numbers.',
        ]);

        // In production, verify against stored code
        // For now, we'll accept any 6-digit code for demo
        $isValidCode = $this->verifyPhoneCode($user, $validated['verification_code']);

        if (!$isValidCode) {
            RateLimiter::hit($key, 300); // 5 minute penalty
            
            Log::warning('phone_verification_failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code. Please try again.'
            ], 422);
        }

        try {
            // Mark phone as verified
            $user->phone_verified_at = now();
            $user->save();

            // Clear rate limiting
            RateLimiter::clear($key);

            Log::info('phone_verified_successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone_masked' => $this->maskPhoneNumber($user->phone),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phone number verified successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('phone_verification_error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify phone number. Please try again.'
            ], 500);
        }
    }

    /**
     * Resend phone verification SMS
     */
    public function resendPhoneVerification(Request $request)
    {
        $user = Auth::user();

        // Rate limiting for resend attempts
        $key = 'phone-resend:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many resend attempts. Please try again in " . ceil($seconds / 60) . " minutes."
            ], 429);
        }

        try {
            $this->sendPhoneVerification($user);
            RateLimiter::hit($key, 60); // 1 minute cooldown for resend
            Log::info('phone_verification_resent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone_masked' => $this->maskPhoneNumber($user->phone),
                'ip' => $request->ip(),
                'timestamp' => Carbon::now()->toISOString()
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Verification code resent successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('phone_resend_error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend verification code. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove phone number from account
     */
    public function removePhone(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required|string'
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            Log::warning('phone_removal_invalid_password', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'The provided password is incorrect.'
            ], 422);
        }

        try {
            $maskedPhone = $this->maskPhoneNumber($user->phone);
            
            $user->phone = null;
            $user->phone_verified_at = null;
            $user->save();

            Log::info('phone_number_removed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone_masked' => $maskedPhone,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phone number removed successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('phone_removal_error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove phone number. Please try again.'
            ], 500);
        }
    }

    /**
     * Update user email.
     */
    public function updateEmail(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password_confirm_email' => 'required|string', // Password confirmation for email change
        ]);

        if (!Hash::check($request->password_confirm_email, $user->password)) {
            Log::channel('security')->warning('email_update_failed_incorrect_password', [
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
                'timestamp' => Carbon::now()->toISOString()
            ]);
            return back()->withErrors(['password_confirm_email' => 'Incorrect password.']);
        }

        $user->email = $request->email;
        $user->email_verified_at = null; // Mark email as unverified until re-verified
        $user->save();

        // TODO: Send email verification notification

        Log::channel('security')->info('email_updated', [
            'user_id' => Auth::id(),
            'new_email' => $user->email,
            'ip' => $request->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return redirect()->back()->with('success', 'Email updated successfully! Please verify your new email address.');
    }

    /**
     * Send phone verification SMS
     */
    private function sendPhoneVerification(User $user)
    {
        // Generate 6-digit verification code
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store verification code in cache with 10-minute expiry
        Cache::put("phone_verification:{$user->id}", $verificationCode, 600);
        
        // In production, send SMS via Twilio or similar service
        // For now, log it (remove in production)
        Log::info('phone_verification_code_generated', [
            'user_id' => $user->id,
            'phone_masked' => $this->maskPhoneNumber($user->phone),
            'code' => $verificationCode, // Remove this in production
            'timestamp' => now()->toISOString()
        ]);
        
        // TODO: Implement actual SMS sending
        // $this->smsService->send($user->phone, "Your verification code is: {$verificationCode}");
    }

    /**
     * Verify phone verification code
     */
    private function verifyPhoneCode(User $user, string $code): bool
    {
        $storedCode = Cache::get("phone_verification:{$user->id}");
        
        if (!$storedCode) {
            return false;
        }
        
        $isValid = hash_equals($storedCode, $code);
        
        if ($isValid) {
            // Clear the verification code
            Cache::forget("phone_verification:{$user->id}");
        }
        
        return $isValid;
    }

    /**
     * Mask phone number for logging
     */
    private function maskPhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }
        
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        $length = strlen($cleaned);
        
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        return substr($cleaned, 0, 3) . str_repeat('*', $length - 6) . substr($cleaned, -3);
    }

    /**
     * API: Get all available pricing plans.
     */
    public function getPricingPlans(Request $request)
    {
        $plans = PricingPlan::active()->ordered()->get();
        return response()->json(['success' => true, 'plans' => $plans]);
    }

    /**
     * API: Get current user subscription details.
     */
    public function getCurrentSubscription(Request $request)
    {
        $user = Auth::user();
        $subscription = null;

        if ($user->currentSubscription) {
            $sub = $user->currentSubscription;
            $plan = $sub->pricingPlan;

            $subscription = [
                'id' => $sub->id,
                'status' => $sub->status,
                'is_in_trial' => $sub->isInTrial(),
                'trial_days_remaining' => $sub->isInTrial() ? $sub->trial_days_remaining : 0,
                'trial_ends_at_formatted' => $sub->isInTrial() ? $sub->trial_ends_at->format('M d, Y') : null,
                'expires_at_formatted' => $sub->expires_at ? $sub->expires_at->format('M d, Y') : null,
                'days_remaining' => $sub->expires_at ? $sub->days_remaining : 0,
                'pricing_plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'formatted_price' => $plan->formatted_price,
                    'period' => $plan->period,
                    'features' => $plan->features, // Include all features
                ],
                // Dummy billing history - integrate with actual payment history if available
                'billing_history' => [
                    ['date' => 'Jul 1, 2024', 'description' => $plan->name . ' Plan', 'amount' => $plan->price, 'currency' => $plan->currency, 'status' => 'Paid'],
                    ['date' => 'Jun 1, 2024', 'description' => $plan->name . ' Plan', 'amount' => $plan->price, 'currency' => $plan->currency, 'status' => 'Paid'],
                ],
            ];
        }

        return response()->json(['success' => true, 'subscription' => $subscription]);
    }

    /**
     * API: Subscribe or upgrade to a plan.
     */
    public function subscribeToPlan(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:pricing_plans,id',
        ]);

        $user = Auth::user();
        $newPlan = PricingPlan::find($validated['plan_id']);

        // Check if user already has an active or trial subscription
        $currentSubscription = $user->currentSubscription;

        // Simple payment simulation for MVP (replace with actual payment gateway integration)
        $paymentSuccess = true; // Assume payment is successful for demo purposes

        if (!$paymentSuccess) {
            return response()->json(['success' => false, 'message' => 'Payment failed. Please try again.'], 400);
        }

        try {
            if ($currentSubscription) {
                // Handle upgrade/change plan
                // For simplicity, we'll cancel the old one and create a new one.
                // In a real system, you might prorate, handle refunds, etc.
                $currentSubscription->update(['status' => 'cancelled', 'expires_at' => now()]);
            }

            $startedAt = now();
            $expiresAt = null;
            $trialEndsAt = null;
            $status = 'active';

            // If the plan is "Essential" and no previous subscription, offer a trial
            if ($newPlan->slug === 'essential' && !$currentSubscription) {
                $status = 'trial';
                $trialEndsAt = $startedAt->copy()->addDays(7); // 7-day trial
                $expiresAt = $startedAt->copy()->addMonth(); // First month after trial
            } elseif ($newPlan->period === 'monthly') {
                $expiresAt = $startedAt->copy()->addMonth();
            } elseif ($newPlan->period === 'yearly') {
                $expiresAt = $startedAt->copy()->addYear();
            }

            $userSubscription = UserSubscription::create([
                'user_id' => $user->id,
                'pricing_plan_id' => $newPlan->id,
                'status' => $status,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'trial_ends_at' => $trialEndsAt,
                'amount_paid' => $newPlan->price,
                'payment_method' => 'simulated',
                'transaction_id' => Str::random(16),
                'metadata' => [
                    'previous_plan_id' => $currentSubscription ? $currentSubscription->pricing_plan_id : null,
                    'change_type' => $currentSubscription ? ($newPlan->price > $currentSubscription->pricingPlan->price ? 'upgrade' : 'downgrade') : 'new',
                ],
            ]);

            // Update user's has_active_subscription flag or cache if you have one
            $user->load('currentSubscription'); // Refresh user's relationship
            
            Log::info('user_subscribed', [
                'user_id' => $user->id,
                'plan_id' => $newPlan->id,
                'plan_name' => $newPlan->name,
                'new_subscription_status' => $userSubscription->status,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json(['success' => true, 'message' => "Successfully subscribed to {$newPlan->name} plan!", 'subscription' => $userSubscription->load('pricingPlan')]);

        } catch (\Exception $e) {
            Log::error('subscription_error', [
                'user_id' => $user->id,
                'plan_id' => $newPlan->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to subscribe. Please try again.'], 500);
        }
    }

    /**
     * API: Cancel user subscription.
     */
    public function cancelSubscription(Request $request)
    {
        $user = Auth::user();
        $currentSubscription = $user->currentSubscription;

        if (!$currentSubscription || !($currentSubscription->isActive() || $currentSubscription->isInTrial())) {
            return response()->json(['success' => false, 'message' => 'No active subscription to cancel.'], 400);
        }

        try {
            // In a real application, integrate with your payment gateway to cancel the subscription.
            // This will usually involve setting the subscription to cancel at the end of the current billing period.
            $currentSubscription->update([
                'status' => 'cancelled',
                // 'expires_at' will remain the same until the end of the period, or set to now() if immediate cancellation is preferred
            ]);

            // Optionally update user's has_active_subscription flag or cache
            $user->load('currentSubscription');

            Log::info('user_cancelled_subscription', [
                'user_id' => $user->id,
                'plan_name' => $currentSubscription->pricingPlan->name,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
            return response()->json(['success' => true, 'message' => 'Subscription cancelled successfully! Your access will continue until the end of the current billing period.']);
        } catch (\Exception $e) {
            Log::error('subscription_cancellation_error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to cancel subscription. Please try again.'], 500);
        }
    }

    /**
     * Handle subscription to a new plan.
     */
    public function subscribe(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'plan_id' => 'required|exists:pricing_plans,id',
        ]);

        $plan = PricingPlan::findOrFail($request->plan_id);

        // Check if user already has an active subscription
        if ($user->currentSubscription && $user->currentSubscription->isActive()) {
            return redirect()->back()->with('error', 'You already have an active subscription. Consider upgrading instead.');
        }

        // For simplicity, we'll assume immediate payment success.
        // In a real app, this would involve payment gateway integration.

        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addDays($plan->duration_days);

        UserSubscription::create([
            'user_id' => $user->id,
            'pricing_plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'payment_status' => 'paid',
            'trial_ends_at' => null, // No trial for direct subscription
        ]);

        Log::channel('security')->info('user_subscribed', [
            'user_id' => Auth::id(),
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return redirect()->back()->with('success', 'Successfully subscribed to ' . $plan->name . '!');
    }

    /**
     * Handle upgrading an existing subscription.
     */
    public function upgradeSubscription(Request $request)
    {
        $user = Auth::user();
        $currentSubscription = $user->currentSubscription;

        if (!$currentSubscription || !$currentSubscription->isActive()) {
            return redirect()->back()->with('error', 'You do not have an active subscription to upgrade.');
        }

        $request->validate([
            'new_plan_id' => 'required|exists:pricing_plans,id',
        ]);

        $newPlan = PricingPlan::findOrFail($request->new_plan_id);

        if ($newPlan->price <= $currentSubscription->pricingPlan->price) {
            return redirect()->back()->with('error', 'You can only upgrade to a higher-priced plan.');
        }

        // For simplicity, we'll assume immediate payment success.
        // In a real app, this would involve payment gateway integration and prorating.

        // End current subscription immediately
        $currentSubscription->update([
            'end_date' => Carbon::now(),
            'status' => 'upgraded',
        ]);

        // Create new subscription
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addDays($newPlan->duration_days);

        UserSubscription::create([
            'user_id' => $user->id,
            'pricing_plan_id' => $newPlan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'payment_status' => 'paid',
            'trial_ends_at' => null,
        ]);

        Log::channel('security')->info('user_upgraded_subscription', [
            'user_id' => Auth::id(),
            'old_plan_id' => $currentSubscription->pricingPlan->id,
            'old_plan_name' => $currentSubscription->pricingPlan->name,
            'new_plan_id' => $newPlan->id,
            'new_plan_name' => $newPlan->name,
            'ip' => request()->ip(),
            'timestamp' => Carbon::now()->toISOString()
        ]);

        return redirect()->back()->with('success', 'Successfully upgraded to ' . $newPlan->name . '!');
    }

    private function getLevelGroupForGrade($grade)
    {
        $mappings = [
            'Primary 1' => 'primary-lower',
            'Primary 2' => 'primary-lower',
            'Primary 3' => 'primary-lower',
            'Primary 4' => 'primary-upper',
            'Primary 5' => 'primary-upper',
            'Primary 6' => 'primary-upper',
            'JHS 1' => 'jhs',
            'JHS 2' => 'jhs',
            'JHS 3' => 'jhs',
            'SHS 1' => 'shs',
            'SHS 2' => 'shs',
            'SHS 3' => 'shs',
            'University Year 1' => 'university',
            'University Year 2' => 'university',
            'University Year 3' => 'university',
            'University Year 4' => 'university',
            'University Year 5' => 'university',
            'University Year 6' => 'university',
        ];
        return $mappings[$grade] ?? null;
    }

    private function hasAccessToLevelGroup($user, $groupId)
    {
        // Superuser has access to all groups
        if ($user->is_superuser) {
            return true;
        }

        // Free access to primary-lower for all users
        if ($groupId === 'primary-lower') {
            return true;
        }

        // Check if user has active subscription or trial
        $currentSubscription = $user->currentSubscription;

        if (!$currentSubscription) {
            return false;
        }

        // All plans have access to all content during active subscription or trial
        return $currentSubscription->isActive() || $currentSubscription->isInTrial();
    }
}
