<?php

namespace App\Livewire\Admin\Users;

use App\Models\Invite;
use App\Models\User;
use App\Notifications\InviteNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Add extends Component
{
    public string $name = '';

    public string $email = '';

    public string $role = 'user';

    public bool $sendInvite = true;

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,user',
        ]);

        // Create the user first (without password since they'll set it via invite)
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make(Str::random(32)), // Temporary password
        ]);

        // Assign role
        $user->assignRole($this->role);

        if ($this->sendInvite) {
            // Debug log the data before creating invitation
            \Log::info('Creating invite with data', [
                'name' => $this->name,
                'email' => $this->email,
                'user_id' => $user->id,
            ]);
            
            // Create invitation
            $invitation = Invite::create([
                'name' => $this->name,
                'email' => $this->email,
                'token' => Str::random(64),
                'user_id' => $user->id,
                'invited_by' => auth()->id(),
                'expires_at' => now()->addHours(24),
            ]);

            \Log::info('Created invite', [
                'invite_id' => $invitation->id,
                'invite_email' => $invitation->email,
                'invite_data' => $invitation->toArray()
            ]);

            // Send invite notification
            $invitation->notify(new InviteNotification($invitation));

            $message = 'User created and invitation sent successfully.';
        } else {
            $message = 'User created successfully (no invitation sent).';
        }

        return redirect()->route('users.index')->with('message', $message);
    }

    public function render()
    {
        return view('livewire.admin.users.add');
    }
}
