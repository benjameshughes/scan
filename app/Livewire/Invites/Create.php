<?php

namespace App\Livewire\Invites;

use App\Models\Invite;
use App\Models\User;
use App\Notifications\InviteNotification;
use Illuminate\Support\Str;
use Livewire\Component;

class Create extends Component
{
    public string $email = '';

    public string $name = '';

    protected $rules = [
        'email' => 'required|email|unique:users,email',
    ];

    public function create()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt(Str::random(32)),
            'status' => false,
        ]);

        $invite = Invite::create([
            'name' => $this->name,
            'email' => $this->email,
            'user_id' => $user->id,
            'invited_by' => auth()->id(),
            'token' => Str::random(64),
            'accepted_at' => null,
            'expires_at' => now()->addHours(24),
        ]);

        $invite->notify(new InviteNotification($invite));

        $this->dispatch('invite-sent');

        redirect()->route('admin.users.index');
    }

    public function render()
    {
        return view('livewire.invites.create');
    }
}
