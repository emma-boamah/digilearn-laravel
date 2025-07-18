<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        return view('dashboard.profile', compact('user', 'maskedPhone', 'availablePlans'));
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
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'phone.regex' => 'Please enter a valid phone number.',
            'country.regex' => 'Country name can only contain letters, spaces, and hyphens.',
            'city.regex' => 'City name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
        ]);

        try {
            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $validated['avatar'] = $avatarPath;
            }

            // Combine first and last name
            $validated['name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);
            unset($validated['first_name'], $validated['last_name']);

            // Sanitize inputs
            $validated['email'] = strtolower(trim($validated['email']));
            if (isset($validated['city'])) {
                $validated['city'] = trim($validated['city']);
            }
            if (isset($validated['bio'])) {
                $validated['bio'] = trim($validated['bio']);
            }

            // Update user
            $user->update($validated);

            // Log the profile update
            Log::info('profile_updated', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully!',
                    'user' => $user->refresh()
                ]);
            }

            return redirect()->back()->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            Log::error('profile_update_error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update profile. Please try again.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update profile. Please try again.');
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
                'timestamp' => now()->toISOString()
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
        // In a real application, fetch active pricing plans from your database
        // For now, return dummy data
        $plans = [
            [
                'id' => 1,
                'name' => 'ESSENTIAL',
                'price' => 0,
                'formatted_price' => 'Free',
                'period' => 'month',
                'description' => 'Access to essential lessons and features.',
                'features' => [
                    'Limited lessons',
                    'Basic quizzes',
                    'Community support',
                ],
            ],
            [
                'id' => 2,
                'name' => 'EXTRA TUTION',
                'price' => 9.99,
                'formatted_price' => 'Ghc 200.00',
                'period' => 'month',
                'description' => 'Unlock all lessons and advanced features.',
                'features' => [
                    'Unlimited lessons',
                    'Advanced quizzes',
                    'Priority support',
                    'Offline access',
                    'Ad-free experience',
                ],
            ],
            [
                'id' => 3,
                'name' => 'HOME SCHOOL',
                'price' => 99.99,
                'formatted_price' => 'Ghc 200.00',
                'period' => 'year',
                'description' => 'Best value for serious learners.',
                'features' => [
                    'All Premium features',
                    'Personalized learning path',
                    '1-on-1 tutoring sessions',
                    'Early access to new content',
                ],
            ],
        ];

        return response()->json(['success' => true, 'plans' => $plans]);
    }

    /**
     * API: Get current user subscription details.
     */
    public function getCurrentSubscription(Request $request)
    {
        $user = Auth::user();
        // In a real application, fetch the user's current subscription from the database
        // For now, return dummy data based on whether the user has a subscription
        $subscription = null;
        if ($user->currentSubscription) { // Assuming user has a currentSubscription relationship
            $sub = $user->currentSubscription;
            $plan = $sub->pricingPlan; // Assuming subscription has a pricingPlan relationship

            $subscription = [
                'id' => $sub->id,
                'status' => $sub->status, // e.g., 'active', 'cancelled', 'trialing'
                'is_in_trial' => $sub->is_in_trial,
                'trial_days_remaining' => $sub->is_in_trial ? now()->diffInDays($sub->trial_ends_at) : 0,
                'trial_ends_at_formatted' => $sub->is_in_trial ? $sub->trial_ends_at->format('M d, Y') : null,
                'expires_at_formatted' => $sub->expires_at ? $sub->expires_at->format('M d, Y') : null,
                'days_remaining' => $sub->expires_at ? now()->diffInDays($sub->expires_at) : 0,
                'pricing_plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'formatted_price' => $plan->formatted_price,
                    'period' => $plan->period,
                ],
                'billing_history' => [
                    // Dummy billing history
                    ['date' => 'Jul 1, 2024', 'description' => 'Premium Plan', 'amount' => '9.99', 'currency' => '$', 'status' => 'Paid'],
                    ['date' => 'Jun 1, 2024', 'description' => 'Premium Plan', 'amount' => '9.99', 'currency' => '$', 'status' => 'Paid'],
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
        $plan = PricingPlan::find($validated['plan_id']);

        // In a real application, integrate with a payment gateway here
        // For demo, simulate success
        try {
            // Logic to create or update user subscription
            // Example: $user->newSubscription('default', $plan->stripe_price_id)->create($paymentMethod);
            Log::info('user_subscribed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'ip' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);
            return response()->json(['success' => true, 'message' => "Successfully subscribed to {$plan->name} plan!"]);
        } catch (\Exception $e) {
            Log::error('subscription_error', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
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

        // In a real application, integrate with your payment gateway to cancel the subscription
        try {
            // Example: $user->subscription('default')->cancel();
            Log::info('user_cancelled_subscription', [
                'user_id' => $user->id,
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
}
