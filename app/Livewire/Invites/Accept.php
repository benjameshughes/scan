<?php

namespace App\Livewire\Invites;

use App\Models\Invite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Component;

class Accept extends Component
{
    public Invite $invite;

    public string $token = '';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount($token, Request $request)
    {
        // Get the invite and make sure it's valid in one query
        $this->invite = Invite::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->with('user')
            ->first();

        // Pre-fill the form if needed
        $this->token = $token;
        $this->name = $this->invite->user->name ?? '';
        $this->email = $this->invite->user->email ?? '';
    }

    public function acceptInvite()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::min(6)->max(255)->letters()->mixedCase()],
        ]);

        // Same thing as users, bypass the guard for accepted_at
        DB::table('invites')
            ->where('id', $this->invite->id)
            ->update([
                'accepted_at' => now(),
            ]);

        $user = $this->invite->user;

        // Bypassing the guard in the model. This is a better option due to keeping the guard in effect and this is the only except to that guard
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'name' => $this->name,
                'password' => Hash::make($this->password),
                'email_verified_at' => now(),
                'status' => true,
            ]);

        // Assign the user role
        $user->syncRoles('user');

        Auth::login($user);

        return $this->redirect(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.invites.accept')->layout('layouts.guest');
    }
}
