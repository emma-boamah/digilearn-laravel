<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Stevebauman\Location\Facades\Location;

class AuthSecurityAlertService
{
    /**
     * Retrieve all super admins and security-monitoring admins.
     */
    public function getAdmins()
    {
        return User::where('is_superuser', true)
            ->orWhereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'restricted-admin']);
            })
            ->get();
    }

    /**
     * Handle and alert on mail delivery failures across the application.
     * Note: Alerts via In-App (database) only to avoid infinite loops if mailer is down.
     */
    public function handleMailFailure(string $source, string $recipientEmail, string $errorMessage, array $context = []): void
    {
        Log::channel('security')->error("mail_delivery_failed: {$source}", array_merge([
            'source' => $source,
            'recipient' => $recipientEmail,
            'error' => $errorMessage,
            'timestamp' => now()->toIso8601String(),
        ], $context));

        $cleanError = $this->simplifyMailError($errorMessage);
        $title = "⚠️ Mail Delivery Failure: {$source}";
        $message = "Failed to deliver email to {$recipientEmail} for {$source}. {$cleanError}";

        $admins = $this->getAdmins();
        if ($admins->isNotEmpty()) {
            try {
                Notification::sendNow($admins, new AdminNotification($title, $message, null, null, ['database']));
            } catch (\Throwable $e) {
                Log::channel('security')->error('Failed to dispatch mail failure database notification: ' . $e->getMessage());
            }
        }
    }

    /**
     * Handle and alert on signup rate limit breaches (potential bot attacks or abuse).
     */
    public function handleSignupRateLimit(Request $request, int $lockoutSeconds): void
    {
        $ip = get_client_ip();
        $email = $request->input('email', 'N/A');
        $minutes = (int) ceil($lockoutSeconds / 60);

        // Detect country if location service available
        $country = 'Unknown';
        try {
            if ($position = Location::get($ip)) {
                $country = $position->countryName ?? $country;
            }
        } catch (\Throwable $e) {
            // Ignore location errors
        }

        // Track attack counter for this IP in cache
        $countKey = "sec_rate_count:{$ip}";
        $attackCount = (int) Cache::increment($countKey);
        Cache::put($countKey, $attackCount, now()->addHours(1));

        // Cooldown check for email alerting (15 minutes per IP to avoid alert storms)
        $cooldownKey = "sec_rate_alert_cooldown:{$ip}";
        $shouldSendEmail = !Cache::has($cooldownKey);

        if ($shouldSendEmail) {
            Cache::put($cooldownKey, true, now()->addMinutes(15));
        }

        $title = "🚨 Security Alert: Signup Rate Limit Exceeded";
        $message = "IP address {$ip} ({$country}) has triggered the signup rate limit with {$attackCount} total attempts. Target email: {$email}. Locked out for {$minutes} minutes.";

        Log::channel('security')->warning('signup_rate_limit_exceeded', [
            'ip' => $ip,
            'email' => $email,
            'country' => $country,
            'user_agent' => $request->userAgent(),
            'attack_count' => $attackCount,
            'lockout_minutes' => $minutes,
        ]);

        $this->dispatchSecurityAlert($title, $message, $shouldSendEmail);
    }

    /**
     * Handle and alert on repeated failed OTP verification attempts (potential brute-force).
     */
    public function handleOtpBruteForce(string $email, string $ip, int $attempts, bool $lockedOut = false): void
    {
        $cooldownKey = "sec_otp_brute_cooldown:{$email}:{$ip}";
        $shouldSendEmail = $lockedOut && !Cache::has($cooldownKey);

        if ($shouldSendEmail) {
            Cache::put($cooldownKey, true, now()->addMinutes(15));
        }

        if ($lockedOut) {
            $title = "🚨 Security Alert: OTP Brute-Force Lockout";
            $message = "Account registration for {$email} was locked out after 5 consecutive invalid OTP submissions from IP {$ip}. Session terminated.";
        } else {
            $title = "⚠️ Security Warning: Multiple Invalid OTP Attempts";
            $message = "{$attempts} consecutive failed OTP verification attempts detected for {$email} from IP {$ip}.";
        }

        Log::channel('security')->warning('otp_verification_failure', [
            'email' => $email,
            'ip' => $ip,
            'attempts' => $attempts,
            'locked_out' => $lockedOut,
            'timestamp' => now()->toIso8601String(),
        ]);

        $this->dispatchSecurityAlert($title, $message, $shouldSendEmail);
    }

    /**
     * Dispatch alert to admins with fallback to database if mail fails.
     */
    protected function dispatchSecurityAlert(string $title, string $message, bool $shouldSendEmail): void
    {
        $admins = $this->getAdmins();
        if ($admins->isEmpty()) {
            return;
        }

        $channels = $shouldSendEmail ? ['database', 'mail'] : ['database'];

        try {
            Notification::sendNow($admins, new AdminNotification($title, $message, null, null, $channels));
        } catch (\Throwable $e) {
            Log::channel('security')->error("Failed to send security alert with email: " . $e->getMessage());
            // Fallback to in-app database notification only if mailer failed
            try {
                Notification::sendNow($admins, new AdminNotification($title, $message, null, null, ['database']));
            } catch (\Throwable $dbE) {
                Log::channel('security')->error("Failed to send database fallback alert: " . $dbE->getMessage());
            }
        }
    }

    /**
     * Simplify raw mail error messages for concise admin view.
     */
    protected function simplifyMailError(string $raw): string
    {
        $lower = strtolower($raw);
        if (str_contains($raw, 'LE_102') || str_contains($lower, 'credit') || str_contains($lower, 'exhausted') || str_contains($lower, 'quota')) {
            return 'ZeptoMail credits or quota exhausted.';
        }
        if (str_contains($raw, '535') || str_contains($lower, 'authentication failed')) {
            return 'Authentication failed with ZeptoMail credentials.';
        }
        if (str_contains($lower, 'connection timed out') || str_contains($lower, 'connection refused')) {
            return 'Connection timed out to mail server.';
        }
        return substr($raw, 0, 150);
    }
}
