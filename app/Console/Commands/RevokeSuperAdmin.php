<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RevokeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:revoke-superadmin {email : The email of the user to revoke privileges from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke super user and admin privileges from a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("No user found with email: {$email}.");
            return 1;
        }

        if (!$user->is_superuser && !$user->hasRole('super-admin')) {
            $this->warn("User {$email} does not currently have superuser or admin privileges. Nothing to revoke.");
            return 0;
        }

        // Revoke superuser status
        $user->is_superuser = false;
        $user->save();

        // Remove Spatie role
        try {
            if ($user->hasRole('super-admin')) {
                $user->removeRole('super-admin');
            }
            $this->info("Successfully revoked superuser and admin privileges from {$email}.");
        } catch (\Exception $e) {
            $this->error("Revoked superuser status, but failed to remove Spatie role: " . $e->getMessage());
        }
        
        return 0;
    }
}
