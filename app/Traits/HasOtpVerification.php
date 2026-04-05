<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpVerificationMail;

trait HasOtpVerification
{
    /**
     * Initiate OTP verification flow
     */
    protected function initiateOtpFlow(Request $request, array $data, string $type = 'standard')
    {
        // Generate 6-digit OTP
        $otp = (string) rand(100000, 999999);
        
        // Prepare registration data
        $registrationData = $data;
        
        // Hash password if it's a standard signup (Google won't have one here)
        if ($type === 'standard' && isset($data['password'])) {
            $registrationData['password'] = Hash::make($data['password']);
        } elseif ($type === 'google') {
             // For Google, we use a random string as password placeholder
             $registrationData['password'] = Hash::make(\Illuminate\Support\Str::random(64));
        }
        
        $registrationData['signup_type'] = $type;
        
        session()->put('registration_otp', [
            'code' => $otp,
            'expires_at' => now()->addMinutes(10),
            'data' => $registrationData
        ]);
        
        // Send OTP email
        $userName = $data['name'] ?? 'User';
        $userEmail = $data['email'];

        try {
            Mail::to($userEmail)->send(new OtpVerificationMail($otp, $userName));
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            Log::error('Failed to send OTP email', ['error' => $errorMsg]);
            
            if (str_contains($errorMsg, '429') || str_contains(strtolower($errorMsg), 'credit') || str_contains(strtolower($errorMsg), 'exhausted') || str_contains($errorMsg, '402') || str_contains($errorMsg, 'TM_5001') || str_contains($errorMsg, 'LE_102')) {
                $superAdmins = \App\Models\User::where('is_superuser', true)->get();
                if ($superAdmins->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send($superAdmins, new \App\Notifications\ZeptoMailErrorNotification($errorMsg));
                }
            }
            
            return back()->withInput()->withErrors(['email' => 'Failed to send verification code due to a temporary service issue. Please try again later.']);
        }
        
        return redirect()->route('verify-otp')->with('otp_email', $userEmail);
    }
}
