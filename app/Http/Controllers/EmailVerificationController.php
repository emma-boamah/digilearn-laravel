<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Carbon\Carbon;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification notice
     */
    public function show(Request $request)
    {
        // If user is already verified, redirect to dashboard
        if ($request->user()->hasVerifiedEmail()) {
            if (session('selected_level')) {
                return redirect()->route('dashboard.main');
            } else {
                return redirect()->route('dashboard.level-selection');
            }
        }

        return view('auth.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified
     */
    public function verify(EmailVerificationRequest $request)
    {
        $user = $request->user();

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            $this->logSecurityEvent('email_already_verified', $request, [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            if (session('selected_level')) {
                return redirect()->route('dashboard.main');
            } else {
                return redirect()->route('dashboard.level-selection');
            }
        }

        // Verify the email
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            $this->logSecurityEvent('email_verified_successfully', $request, [
                'user_id' => $user->id,
                'email' => $user->email,
                'verified_at' => Carbon::now()->toISOString()
            ]);
        }

        return redirect()->route('dashboard.level-selection')->with('verified', true);
    }

    /**
     * Resend the email verification notification
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            if (session('selected_level')) {
                return redirect()->route('dashboard.main');
            } else {
                return redirect()->route('dashboard.level-selection');
            }
        }

        // Send verification email
        $user->sendEmailVerificationNotification();

        $this->logSecurityEvent('email_verification_resent', $request, [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return back()->with('resent', true);
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