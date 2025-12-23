<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class MonitorAuthLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:monitor
                            {--hours=24 : Hours to look back}
                            {--log=auth : Which log to monitor (auth, security, google_auth)}
                            {--alert : Send alerts when security thresholds are exceeded}
                            {--silent : Run silently without console output}
                            {--email= : Email address to send alerts to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor authentication logs for security and debugging with automated alerting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = $this->option('hours');
        $logType = $this->option('log');
        $alert = $this->option('alert');
        $silent = $this->option('silent');
        $email = $this->option('email');
        $since = now()->subHours($hours);

        if (!$silent) {
            $this->info("🔍 Monitoring {$logType} logs (last {$hours} hours):");
            $this->info(str_repeat('=', 60));
        }

        $logFile = $this->getLogFilePath($logType);

        if (!File::exists($logFile)) {
            if (!$silent) {
                $this->error("Log file not found: {$logFile}");
            }
            return;
        }

        $stats = $this->analyzeLogFile($logFile, $since, $logType, $silent);

        // Check for alerts if requested
        if ($alert) {
            $this->checkAlerts($stats, $logType, $hours, $email, $silent);
        }
    }

    /**
     * Get the log file path for the specified log type
     */
    private function getLogFilePath(string $logType): string
    {
        $logFiles = [
            'auth' => storage_path('logs/auth.log'),
            'security' => storage_path('logs/security.log'),
            'google_auth' => storage_path('logs/google_auth.log'),
        ];

        return $logFiles[$logType] ?? storage_path('logs/laravel.log');
    }

    /**
     * Analyze the log file and display relevant information
     */
    private function analyzeLogFile(string $logFile, Carbon $since, string $logType, bool $silent = false): array
    {
        $content = File::get($logFile);
        $lines = explode("\n", $content);

        $stats = [
            'total_events' => 0,
            'errors' => 0,
            'warnings' => 0,
            'successful_logins' => 0,
            'failed_logins' => 0,
            'rate_limits' => 0,
            'account_locks' => 0,
            'google_auth_failures' => 0,
        ];

        $recentEvents = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Parse log line (assuming Laravel's log format)
            if ($this->isRecentLogLine($line, $since)) {
                $stats['total_events']++;

                $parsedEvent = $this->parseLogLine($line);
                if ($parsedEvent) {
                    $recentEvents[] = $parsedEvent;

                    // Update statistics
                    $this->updateStats($stats, $parsedEvent);
                }
            }
        }

        if (!$silent) {
            $this->displayStats($stats, $logType);
            $this->displayRecentEvents($recentEvents, $logType);
            $this->displayRecommendations($stats);
        }

        return $stats;
    }

    /**
     * Check if a log line is recent
     */
    private function isRecentLogLine(string $line, Carbon $since): bool
    {
        // Extract timestamp from log line (Laravel format: [YYYY-MM-DD HH:mm:ss])
        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
            $logTime = Carbon::createFromFormat('Y-m-d H:i:s', $matches[1]);
            return $logTime->gte($since);
        }

        return false;
    }

    /**
     * Parse a log line to extract event information
     */
    private function parseLogLine(string $line): ?array
    {
        // Extract JSON part from log line
        if (preg_match('/\{.*\}/s', $line, $matches)) {
            $jsonPart = $matches[0];

            try {
                $data = json_decode($jsonPart, true);
                return $data;
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Update statistics based on parsed event
     */
    private function updateStats(array &$stats, array $event): void
    {
        $eventName = $event['event'] ?? '';
        $category = $event['category'] ?? '';

        switch ($eventName) {
            case 'successful_login':
                $stats['successful_logins']++;
                break;
            case 'failed_login':
                $stats['failed_logins']++;
                break;
            case 'rate_limit_exceeded':
            case 'signup_rate_limit':
                $stats['rate_limits']++;
                break;
            case 'account_locked':
                $stats['account_locks']++;
                break;
        }

        if (str_contains($eventName, 'google') && $category !== 'success') {
            $stats['google_auth_failures']++;
        }

        if (in_array($category, ['error', 'system', 'validation'])) {
            $stats['errors']++;
        }

        if ($category === 'warning') {
            $stats['warnings']++;
        }
    }

    /**
     * Display statistics
     */
    private function displayStats(array $stats, string $logType): void
    {
        $this->info("\n📊 Statistics:");
        $this->line("Total Events: <comment>{$stats['total_events']}</comment>");
        $this->line("Successful Logins: <info>{$stats['successful_logins']}</info>");
        $this->line("Failed Logins: <error>{$stats['failed_logins']}</error>");
        $this->line("Rate Limit Hits: <warning>{$stats['rate_limits']}</warning>");
        $this->line("Account Locks: <error>{$stats['account_locks']}</error>");

        if ($logType === 'google_auth') {
            $this->line("Google Auth Failures: <error>{$stats['google_auth_failures']}</error>");
        }

        $this->line("Errors: <error>{$stats['errors']}</error>");
        $this->line("Warnings: <warning>{$stats['warnings']}</warning>");
    }

    /**
     * Display recent events
     */
    private function displayRecentEvents(array $recentEvents, string $logType): void
    {
        if (empty($recentEvents)) {
            $this->info("\n📝 No recent events found.");
            return;
        }

        $this->info("\n📝 Recent Events (last 10):");

        $displayEvents = array_slice($recentEvents, -10);

        foreach ($displayEvents as $event) {
            $eventName = $event['event'] ?? 'unknown';
            $category = $event['category'] ?? 'unknown';
            $email = $event['email'] ?? 'N/A';
            $ip = $event['ip'] ?? 'N/A';

            $color = $this->getEventColor($category);

            $this->line("• <{$color}>{$eventName}</{$color}> - {$email} ({$ip})");
        }
    }

    /**
     * Display security recommendations
     */
    private function displayRecommendations(array $stats): void
    {
        $this->info("\n💡 Recommendations:");

        if ($stats['failed_logins'] > $stats['successful_logins'] * 0.5) {
            $this->warn("⚠️  High ratio of failed to successful logins detected. Check for brute force attempts.");
        }

        if ($stats['rate_limits'] > 10) {
            $this->warn("⚠️  High rate limiting activity. Consider reviewing rate limit settings.");
        }

        if ($stats['account_locks'] > 5) {
            $this->warn("⚠️  Multiple account locks detected. Review account lockout policies.");
        }

        if ($stats['errors'] > $stats['total_events'] * 0.1) {
            $this->warn("⚠️  High error rate detected. Check system health and error logs.");
        }

        $this->info("✅ Check storage/logs/ for detailed logs");
        $this->info("✅ Monitor security.log for suspicious activity");
        $this->info("✅ Review auth.log for authentication patterns");
    }

    /**
     * Get color for event display
     */
    private function getEventColor(string $category): string
    {
        return match ($category) {
            'success' => 'info',
            'error', 'system' => 'error',
            'warning' => 'warning',
            'rate_limit', 'invalid_credentials', 'account_locked' => 'error',
            default => 'comment'
        };
    }

    /**
     * Check for security alerts and send notifications
     */
    private function checkAlerts(array $stats, string $logType, int $hours, ?string $email, bool $silent): void
    {
        $alerts = [];

        // Define alert thresholds
        $thresholds = [
            'failed_logins_ratio' => 0.3, // 30% failed to successful ratio
            'rate_limits' => 5, // More than 5 rate limit hits
            'account_locks' => 3, // More than 3 account locks
            'errors_percentage' => 0.15, // 15% error rate
            'google_auth_failures' => 3, // More than 3 Google auth failures
        ];

        // Check failed login ratio
        if ($stats['successful_logins'] > 0) {
            $ratio = $stats['failed_logins'] / $stats['successful_logins'];
            if ($ratio > $thresholds['failed_logins_ratio']) {
                $alerts[] = [
                    'type' => 'high_failed_login_ratio',
                    'severity' => 'warning',
                    'message' => sprintf(
                        'High failed login ratio: %.1f%% (%d failed vs %d successful)',
                        $ratio * 100,
                        $stats['failed_logins'],
                        $stats['successful_logins']
                    )
                ];
            }
        }

        // Check rate limiting
        if ($stats['rate_limits'] > $thresholds['rate_limits']) {
            $alerts[] = [
                'type' => 'high_rate_limiting',
                'severity' => 'warning',
                'message' => sprintf(
                    'High rate limiting activity: %d attempts blocked',
                    $stats['rate_limits']
                )
            ];
        }

        // Check account locks
        if ($stats['account_locks'] > $thresholds['account_locks']) {
            $alerts[] = [
                'type' => 'multiple_account_locks',
                'severity' => 'critical',
                'message' => sprintf(
                    'Multiple account locks detected: %d accounts locked',
                    $stats['account_locks']
                )
            ];
        }

        // Check error rate
        if ($stats['total_events'] > 0) {
            $errorRate = $stats['errors'] / $stats['total_events'];
            if ($errorRate > $thresholds['errors_percentage']) {
                $alerts[] = [
                    'type' => 'high_error_rate',
                    'severity' => 'warning',
                    'message' => sprintf(
                        'High error rate: %.1f%% (%d errors out of %d events)',
                        $errorRate * 100,
                        $stats['errors'],
                        $stats['total_events']
                    )
                ];
            }
        }

        // Check Google auth failures
        if ($stats['google_auth_failures'] > $thresholds['google_auth_failures']) {
            $alerts[] = [
                'type' => 'google_auth_failures',
                'severity' => 'warning',
                'message' => sprintf(
                    'Multiple Google authentication failures: %d failures',
                    $stats['google_auth_failures']
                )
            ];
        }

        // Send alerts if any
        if (!empty($alerts)) {
            $this->sendAlerts($alerts, $logType, $hours, $email, $silent);
        } elseif (!$silent) {
            $this->info('✅ No security alerts detected.');
        }
    }

    /**
     * Send security alerts via email or console
     */
    private function sendAlerts(array $alerts, string $logType, int $hours, ?string $email, bool $silent): void
    {
        $subject = "🚨 Security Alert: {$logType} Log Analysis (" . now()->format('Y-m-d H:i') . ")";
        $body = $this->buildAlertEmail($alerts, $logType, $hours);

        if ($email) {
            // Send email alert
            try {
                \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($email, $subject) {
                    $message->to($email)
                            ->subject($subject);
                });

                if (!$silent) {
                    $this->info("📧 Security alerts sent to: {$email}");
                }
            } catch (\Exception $e) {
                if (!$silent) {
                    $this->error("Failed to send email alert: " . $e->getMessage());
                }
            }
        } else {
            // Display alerts in console
            if (!$silent) {
                $this->error("\n🚨 SECURITY ALERTS DETECTED:");
                $this->line(str_repeat('=', 60));

                foreach ($alerts as $alert) {
                    $severity = strtoupper($alert['severity']);
                    $this->line("{$severity}: {$alert['message']}");
                }

                $this->line("\n💡 Run with --email=admin@example.com to receive email alerts");
            }
        }
    }

    /**
     * Build the alert email body
     */
    private function buildAlertEmail(array $alerts, string $logType, int $hours): string
    {
        $body = "Security Alert Report\n";
        $body .= "====================\n\n";
        $body .= "Log Type: {$logType}\n";
        $body .= "Time Period: Last {$hours} hours\n";
        $body .= "Generated: " . now()->format('Y-m-d H:i:s T') . "\n\n";

        $body .= "Alerts Detected:\n";
        $body .= "----------------\n";

        foreach ($alerts as $alert) {
            $body .= "• " . strtoupper($alert['severity']) . ": {$alert['message']}\n";
        }

        $body .= "\nRecommendations:\n";
        $body .= "-----------------\n";
        $body .= "• Check the detailed logs in storage/logs/{$logType}.log\n";
        $body .= "• Review recent authentication activity\n";
        $body .= "• Consider adjusting security policies if needed\n";
        $body .= "• Monitor for suspicious IP addresses\n\n";

        $body .= "This is an automated security monitoring alert.\n";

        return $body;
    }
}
