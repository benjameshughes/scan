<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class UserPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:permissions {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give permissions for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get email from argument or env
        $email = $this->argument('email');

        if (!$email) {
            $this->error('No admin email provided. Use --email=your@email.com or set ADMIN_EMAIL in .env');
            return 1;
        }

        // Find the user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }

        // Make sure admin role exists
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $adminRole = Role::create(['name' => 'admin']);
            $this->info('Created admin role');
        }

        // Assign admin role
        $user->assignRole($adminRole);
        $this->info("Successfully assigned admin role to {$user->name} ({$email})");

        return 0;
    }
}
