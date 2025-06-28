<?php

namespace App\Livewire\Admin\Users;

use App\Notifications\InviteNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use WorkOS\Resource\Invitation;

class Add extends Component
{

    public string $name;
    public string $email;
    public Role $role;

    public function create()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $invitation = Invitation::create([
            'name' => $this->name,
            'email' => $this->email,
            'token' => Str::random(64),
            'invited_by' => auth()->id(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($this->email)->send(new InviteNotification($invitation));

        return redirect()->route('admin.users.index')->with('success', 'User invited');
    }

    public function render()
    {
        return view('livewire.admin.users.add');
    }
}