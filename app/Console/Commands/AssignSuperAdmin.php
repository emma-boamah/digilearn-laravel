<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AssignSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-superadmin {email=joeanimgh@gmail.com : The email of the user to upgrade}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign super user and admin privileges to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("No user found with email: {$email}. Make sure they register on the webapp first!");
            return 1;
        }

        // Grant superuser status
        $user->is_superuser = true;
        $user->save();

        // Assign Spatie roles
        try {
            $user->assignRole('super-admin');
            $this->info("Successfully granted superuser and admin privileges to {$email}.");
        } catch (\Exception $e) {
            $this->error("Granted superuser status, but failed to assign Spatie roles: " . $e->getMessage());
        }
        
        return 0;
    }
}
