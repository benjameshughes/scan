<?php

namespace App\Livewire\Profile;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ProfileInformationForm extends Component
{
    use AuthorizesRequests;

    public string $name = '';

    public string $email = '';

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile()
    {
        $user = auth()->user();
        $this->authorize('update', $user);

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
        ]);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->dispatch('profile-updated');
    }

    public function render()
    {
        return view('livewire.profile.profile-information-form');
    }
}
