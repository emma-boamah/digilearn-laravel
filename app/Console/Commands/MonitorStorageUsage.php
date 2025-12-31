<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StorageMonitoringSetting;
use App\Models\StorageAnalytic;
use App\Models\StorageAlert;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MonitorStorageUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:monitor
                            {--setting=default : The monitoring setting to use}
                            {--force : Force monitoring even if not scheduled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor storage usage and send alerts based on configured thresholds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settingName = $this->option('setting');
        $force = $this->option('force');

        // Get monitoring settings
        $setting = StorageMonitoringSetting::where('name', $settingName)->first();

        if (!$setting) {
            $this->error("Storage monitoring setting '{$settingName}' not found");
            return 1;
        }

        if (!$setting->is_active && !$force) {
            $this->info("Storage monitoring setting '{$settingName}' is inactive");
            return 0;
        }

        $this->info("Starting storage monitoring with setting: {$setting->name}");

        $monitoredPaths = $setting->monitored_paths ?? [];
        $alertsSent = 0;
        $analyticsRecorded = 0;

        foreach ($monitoredPaths as $path) {
            try {
                $this->info("Monitoring path: {$path}");

                // Get storage information
                $storageInfo = $this->getStorageInfo($path);

                if (!$storageInfo) {
                    $this->warn("Could not get storage info for path: {$path}");
                    continue;
                }

                // Record analytics
                $analytic = $this->recordStorageAnalytic($path, $storageInfo);
                $analyticsRecorded++;

                // Check for alerts
                $alertSent = $this->checkAndSendAlerts($setting, $path, $storageInfo);
                if ($alertSent) {
                    $alertsSent++;
                }

                // Check for recovery
                $this->checkAndSendRecoveryAlerts($setting, $path, $storageInfo);

                // Run cleanup if enabled and threshold reached
                if ($setting->auto_cleanup_enabled && $storageInfo['usage_percentage'] >= $setting->cleanup_threshold) {
                    $this->runAutoCleanup($setting, $path, $storageInfo);
                }

            } catch (\Exception $e) {
                $this->error("Error monitoring path {$path}: " . $e->getMessage());
                Log::error('Storage monitoring error', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Storage monitoring completed. Analytics recorded: {$analyticsRecorded}, Alerts sent: {$alertsSent}");

        return 0;
    }

    /**
     * Get storage information for a path
     */
    private function getStorageInfo(string $path): ?array
    {
        try {
            // For Laravel storage paths, use disk_total_space and disk_free_space
            if (str_starts_with($path, storage_path())) {
                $totalSpace = disk_total_space($path);
                $freeSpace = disk_free_space($path);

                if ($totalSpace === false || $freeSpace === false) {
                    return null;
                }

                $usedSpace = $totalSpace - $freeSpace;
                $usagePercentage = round(($usedSpace / $totalSpace) * 100, 2);

                return [
                    'total_space_bytes' => $totalSpace,
                    'used_space_bytes' => $usedSpace,
                    'free_space_bytes' => $freeSpace,
                    'usage_percentage' => $usagePercentage,
                    'file_counts' => $this->getFileCounts($path)
                ];
            }

            // For other paths, try to get info if accessible
            if (is_dir($path) && is_readable($path)) {
                $totalSpace = disk_total_space($path);
                $freeSpace = disk_free_space($path);

                if ($totalSpace === false || $freeSpace === false) {
                    return null;
                }

                $usedSpace = $totalSpace - $freeSpace;
                $usagePercentage = round(($usedSpace / $totalSpace) * 100, 2);

                return [
                    'total_space_bytes' => $totalSpace,
                    'used_space_bytes' => $usedSpace,
                    'free_space_bytes' => $freeSpace,
                    'usage_percentage' => $usagePercentage,
                    'file_counts' => $this->getFileCounts($path)
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error getting storage info', ['path' => $path, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get file counts by type
     */
    private function getFileCounts(string $path): array
    {
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            $counts = [
                'total_files' => 0,
                'total_dirs' => 0,
                'by_extension' => []
            ];

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $counts['total_files']++;
                    $extension = strtolower($file->getExtension());
                    $counts['by_extension'][$extension] = ($counts['by_extension'][$extension] ?? 0) + 1;
                } elseif ($file->isDir()) {
                    $counts['total_dirs']++;
                }
            }

            return $counts;

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Record storage analytics
     */
    private function recordStorageAnalytic(string $path, array $storageInfo): StorageAnalytic
    {
        // Calculate growth rate
        $previousAnalytic = StorageAnalytic::where('path', $path)
            ->latest('measured_at')
            ->first();

        $growthRate = null;
        if ($previousAnalytic) {
            $timeDiff = now()->diffInHours($previousAnalytic->measured_at);
            if ($timeDiff > 0) {
                $usageDiff = $storageInfo['usage_percentage'] - $previousAnalytic->usage_percentage;
                $growthRate = round($usageDiff / $timeDiff, 2);
            }
        }

        return StorageAnalytic::create([
            'path' => $path,
            'total_space_bytes' => $storageInfo['total_space_bytes'],
            'used_space_bytes' => $storageInfo['used_space_bytes'],
            'free_space_bytes' => $storageInfo['free_space_bytes'],
            'usage_percentage' => $storageInfo['usage_percentage'],
            'growth_rate_percentage' => $growthRate,
            'file_counts' => $storageInfo['file_counts'],
            'measured_at' => now()
        ]);
    }

    /**
     * Check and send alerts based on thresholds
     */
    private function checkAndSendAlerts(StorageMonitoringSetting $setting, string $path, array $storageInfo): bool
    {
        $usagePercentage = $storageInfo['usage_percentage'];
        $alertType = $setting->getAlertLevel($usagePercentage);

        if (!$alertType) {
            return false; // No alert needed
        }

        // Check if alert should be throttled
        if ($setting->shouldThrottleAlert($alertType, $path)) {
            $this->info("Alert throttled for {$alertType} on {$path}");
            return false;
        }

        // Send alert
        return $this->sendStorageAlert($setting, $alertType, $path, $storageInfo);
    }

    /**
     * Check and send recovery alerts
     */
    private function checkAndSendRecoveryAlerts(StorageMonitoringSetting $setting, string $path, array $storageInfo): bool
    {
        $usagePercentage = $storageInfo['usage_percentage'];

        // Check if we're below recovery threshold
        if ($usagePercentage >= $setting->recovery_threshold) {
            return false; // Still above recovery threshold
        }

        // Check if we recently sent a non-recovery alert
        $lastAlert = StorageAlert::where('path', $path)
            ->where('alert_type', '!=', 'recovery')
            ->latest('alert_sent_at')
            ->first();

        if (!$lastAlert) {
            return false; // No previous alert to recover from
        }

        // Check if recovery alert should be throttled (less aggressive throttling for recovery)
        $recoveryThrottleMinutes = max(60, $setting->alert_throttle_minutes / 4); // At least 1 hour, max 6 hours
        $minutesSinceLastAlert = $lastAlert->alert_sent_at->diffInMinutes(now());

        if ($minutesSinceLastAlert < $recoveryThrottleMinutes) {
            return false;
        }

        // Send recovery alert
        return $this->sendStorageAlert($setting, 'recovery', $path, $storageInfo);
    }

    /**
     * Send storage alert
     */
    private function sendStorageAlert(StorageMonitoringSetting $setting, string $alertType, string $path, array $storageInfo): bool
    {
        try {
            // Get admin users
            $adminUsers = User::where('is_admin', true)->get();

            if ($adminUsers->isEmpty()) {
                $this->warn('No admin users found to send storage alert');
                return false;
            }

            // Create notification
            $notification = new \App\Notifications\StorageAlertNotification(
                $storageInfo['usage_percentage'],
                $this->formatBytes($storageInfo['used_space_bytes']),
                $this->formatBytes($storageInfo['total_space_bytes']),
                $alertType,
                $path
            );

            // Send notification
            $notificationService = app(NotificationService::class);
            $notificationService->sendToUsers($adminUsers, $notification, ['database', 'mail']);

            // Record alert
            StorageAlert::create([
                'alert_type' => $alertType,
                'path' => $path,
                'usage_percentage' => $storageInfo['usage_percentage'],
                'used_space_bytes' => $storageInfo['used_space_bytes'],
                'total_space_bytes' => $storageInfo['total_space_bytes'],
                'admin_users_notified' => $adminUsers->pluck('id')->toArray(),
                'alert_sent_at' => now()
            ]);

            $this->info("{$alertType} alert sent for {$path} ({$storageInfo['usage_percentage']}%)");

            Log::info('Storage alert sent', [
                'alert_type' => $alertType,
                'path' => $path,
                'usage_percentage' => $storageInfo['usage_percentage'],
                'admin_count' => $adminUsers->count()
            ]);

            return true;

        } catch (\Exception $e) {
            $this->error("Failed to send {$alertType} alert for {$path}: " . $e->getMessage());
            Log::error('Storage alert failed', [
                'alert_type' => $alertType,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Run automated cleanup
     */
    private function runAutoCleanup(StorageMonitoringSetting $setting, string $path, array $storageInfo): void
    {
        try {
            $this->info("Running auto cleanup for {$path}");

            $cleanupRules = $setting->cleanup_rules ?? [];

            // Default cleanup rules if none specified
            if (empty($cleanupRules)) {
                $cleanupRules = [
                    'delete_temp_files_older_than_days' => 7,
                    'delete_log_files_older_than_days' => 30,
                    'delete_cache_files_older_than_hours' => 24
                ];
            }

            $filesDeleted = 0;
            $spaceFreed = 0;

            // Delete temp files
            if (isset($cleanupRules['delete_temp_files_older_than_days'])) {
                $days = $cleanupRules['delete_temp_files_older_than_days'];
                $tempPath = storage_path('app/temp');

                if (is_dir($tempPath)) {
                    $files = glob($tempPath . '/*');
                    foreach ($files as $file) {
                        if (is_file($file) && filemtime($file) < strtotime("-{$days} days")) {
                            $fileSize = filesize($file);
                            if (unlink($file)) {
                                $filesDeleted++;
                                $spaceFreed += $fileSize;
                            }
                        }
                    }
                }
            }

            // Delete old log files
            if (isset($cleanupRules['delete_log_files_older_than_days'])) {
                $days = $cleanupRules['delete_log_files_older_than_days'];
                $logPath = storage_path('logs');

                if (is_dir($logPath)) {
                    $files = glob($logPath . '/*.log');
                    foreach ($files as $file) {
                        if (is_file($file) && filemtime($file) < strtotime("-{$days} days")) {
                            $fileSize = filesize($file);
                            if (unlink($file)) {
                                $filesDeleted++;
                                $spaceFreed += $fileSize;
                            }
                        }
                    }
                }
            }

            // Delete old cache files
            if (isset($cleanupRules['delete_cache_files_older_than_hours'])) {
                $hours = $cleanupRules['delete_cache_files_older_than_hours'];
                $cachePath = storage_path('framework/cache/data');

                if (is_dir($cachePath)) {
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($cachePath, \RecursiveDirectoryIterator::SKIP_DOTS)
                    );

                    foreach ($iterator as $file) {
                        if ($file->isFile() && $file->getMTime() < strtotime("-{$hours} hours")) {
                            $fileSize = $file->getSize();
                            if (unlink($file->getPathname())) {
                                $filesDeleted++;
                                $spaceFreed += $fileSize;
                            }
                        }
                    }
                }
            }

            if ($filesDeleted > 0) {
                $this->info("Auto cleanup completed: {$filesDeleted} files deleted, " . $this->formatBytes($spaceFreed) . " freed");
                Log::info('Auto cleanup completed', [
                    'path' => $path,
                    'files_deleted' => $filesDeleted,
                    'space_freed' => $spaceFreed
                ]);
            }

        } catch (\Exception $e) {
            $this->error("Auto cleanup failed for {$path}: " . $e->getMessage());
            Log::error('Auto cleanup failed', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
