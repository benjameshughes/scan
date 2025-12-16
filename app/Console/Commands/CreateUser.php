<?php

namespace App\Console\Commands;

use App\Models\Invite;
use App\Models\User;
use App\Notifications\InviteNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CreateUser extends Command
{
    protected $signature = 'user:create
                            {--email= : The email address for the new user}
                            {--name= : The name for the new user (optional, defaults to email username)}
                            {--role=user : The role to assign (admin, user, stock_manager, supervisor, warehouse_worker)}
                            {--no-invite : Skip sending the invite email}
                            {--confirm : Skip confirmation prompt}';

    protected $description = 'Create a new user and send an invitation email';

    public function handle(): int
    {
        // Get or prompt for email
        $email = $this->option('email') ?? $this->ask('Enter the email address');

        if (! $email) {
            $this->error('Email is required.');

            return self::FAILURE;
        }

        // Validate email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // Get or derive name
        $name = $this->option('name') ?? Str::before($email, '@');

        // Get and validate role
        $role = $this->option('role');
        $availableRoles = Role::pluck('name')->toArray();

        if (! in_array($role, $availableRoles)) {
            $this->error("Invalid role: {$role}");
            $this->info('Available roles: '.implode(', ', $availableRoles));

            return self::FAILURE;
        }

        $sendInvite = ! $this->option('no-invite');

        // Show summary and confirm
        $this->newLine();
        $this->info('User Details:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Email', $email],
                ['Role', $role],
                ['Send Invite', $sendInvite ? 'Yes' : 'No'],
            ]
        );

        if (! $this->option('confirm') && ! $this->confirm('Create this user?', true)) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        // Create the user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'status' => false,
        ]);

        // Assign role
        $user->assignRole($role);

        $this->info("User created: {$user->email}");

        // Send invite if requested
        if ($sendInvite) {
            $invitation = Invite::create([
                'name' => $name,
                'email' => $email,
                'token' => Str::random(64),
                'user_id' => $user->id,
                'invited_by' => null, // CLI doesn't have authenticated user
                'expires_at' => now()->addHours(24),
            ]);

            $invitation->notify(new InviteNotification($invitation));

            $this->info('Invitation email sent.');
        }

        $this->newLine();
        $this->info('Done!');

        return self::SUCCESS;
    }
}
