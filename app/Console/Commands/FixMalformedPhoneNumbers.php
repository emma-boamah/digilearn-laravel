<?php

namespace App\Console\Commands;

use App\Helpers\PhoneNormalizerHelper;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FixMalformedPhoneNumbers extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fix:phone-numbers
                            {--dry-run : Preview changes without modifying the database}
                            {--verbose-log : Log detailed before/after for each number}';

    /**
     * The console command description.
     */
    protected $description = 'Normalize malformed phone numbers in the users table (e.g., +233233559500321 → +233559500321)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $verboseLog = $this->option('verbose-log');

        $this->info($dryRun ? '🔍 DRY RUN — no changes will be saved.' : '🔧 Fixing malformed phone numbers...');
        $this->newLine();

        $users = User::whereNotNull('phone')->where('phone', '!=', '')->get();
        $this->info("Found {$users->count()} users with phone numbers.");

        $fixed = 0;
        $skipped = 0;
        $duplicates = 0;
        $errors = [];

        $seenNumbers = [];

        foreach ($users as $user) {
            $original = $user->phone;
            $result = PhoneNormalizerHelper::inferAndNormalize($original);
            $normalized = $result['normalized'];

            // Skip if already correct
            if ($normalized === $original) {
                $skipped++;
                continue;
            }

            // Check for duplicates: would normalizing this create a conflict?
            if (isset($seenNumbers[$normalized])) {
                $duplicates++;
                $conflictUserId = $seenNumbers[$normalized];
                $this->warn("  ⚠ DUPLICATE: User #{$user->id} ({$original} → {$normalized}) conflicts with User #{$conflictUserId}");
                $errors[] = [
                    'user_id' => $user->id,
                    'original' => $original,
                    'normalized' => $normalized,
                    'conflict_user_id' => $conflictUserId,
                ];
                continue;
            }

            // Also check database for existing normalized number belonging to another user
            $existingUser = User::where('phone', $normalized)->where('id', '!=', $user->id)->first();
            if ($existingUser) {
                $duplicates++;
                $this->warn("  ⚠ DB DUPLICATE: User #{$user->id} ({$original} → {$normalized}) conflicts with existing User #{$existingUser->id}");
                $errors[] = [
                    'user_id' => $user->id,
                    'original' => $original,
                    'normalized' => $normalized,
                    'conflict_user_id' => $existingUser->id,
                ];
                continue;
            }

            $seenNumbers[$normalized] = $user->id;

            if ($verboseLog || $this->getOutput()->isVerbose()) {
                $this->line("  User #{$user->id}: {$original} → {$normalized}");
            }

            if (!$dryRun) {
                $user->phone = $normalized;
                $user->save();

                Log::info('phone_number_normalized', [
                    'user_id' => $user->id,
                    'original' => $original,
                    'normalized' => $normalized,
                    'country_code' => $result['country_code'],
                ]);
            }

            $fixed++;
        }

        $this->newLine();
        $this->info("✅ Results:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total with phone', $users->count()],
                ['Already correct', $skipped],
                [$dryRun ? 'Would fix' : 'Fixed', $fixed],
                ['Duplicate conflicts (skipped)', $duplicates],
            ]
        );

        if (!empty($errors)) {
            $this->newLine();
            $this->warn("⚠ The following users had duplicate conflicts and need manual review:");
            $this->table(
                ['User ID', 'Original', 'Normalized', 'Conflicts With'],
                array_map(fn($e) => [$e['user_id'], $e['original'], $e['normalized'], $e['conflict_user_id']], $errors)
            );

            Log::warning('phone_normalization_duplicates', ['conflicts' => $errors]);
        }

        if ($dryRun && $fixed > 0) {
            $this->newLine();
            $this->info("💡 Run without --dry-run to apply these fixes:");
            $this->line("   php artisan fix:phone-numbers");
        }

        return self::SUCCESS;
    }
}
