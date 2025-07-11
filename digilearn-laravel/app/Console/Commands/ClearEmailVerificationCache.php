<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailVerificationService;

class ClearEmailVerificationCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:cache-clear {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear email verification cache';

    /**
     * Execute the console command.
     */
    public function handle(EmailVerificationService $service)
    {
        if ($email = $this->argument('email')) {
            $service->clearCacheForEmail($email);
            $this->info("Cleared cache for: $email");
        } else {
            $service->clearAllCache();
            $this->info('Cleared all email verification cache');
        }
    }
}
