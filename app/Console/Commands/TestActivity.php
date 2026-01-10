<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;

class TestActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user activity tracking by logging in as first user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::first();
        if (!$user) {
            $this->error('No users found in database');
            return;
        }

        $this->info("Logging in as user: {$user->name} (ID: {$user->id})");

        // Simulate login
        Auth::login($user);

        $this->info('User logged in successfully');

        // Check recent activities
        $activities = UserActivity::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        if ($activities->isEmpty()) {
            $this->warn('No activities found for this user');
        } else {
            $this->info('Recent activities:');
            foreach ($activities as $activity) {
                $this->line("- {$activity->type}: {$activity->description} at {$activity->created_at}");
            }
        }

        return 0;
    }
}