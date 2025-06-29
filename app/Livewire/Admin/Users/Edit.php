<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    public User $user;

    // Store all available role names
    public Collection $roles;

    // Store the name of the role currently selected in the form
    public string $selectedRole = ''; // Initialize as empty string

    // User data
    public array $form = [
        'name',
        'email',
        'password',
    ];

    public function mount(User $user)
    {
        $this->user = $user;
        // Get all role names. We only need the names for display and syncing.
        $this->roles = Role::pluck('name', 'name'); // Use pluck('name', 'name') for easier key access if needed

        $this->form['name'] = $user->name;
        $this->form['email'] = $user->email;

        $currentRoleName = $this->user->roles->first()?->name;
        if ($currentRoleName) {
            $this->selectedRole = $currentRoleName;
        }
    }

    // Removed ensureDefaultRole - handled in mount and update

    public function updateUser()
    {

        $validated = $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'optional',
        ]);

        $this->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (! empty($this->selectedRole)) {
            $this->user->syncRoles([$this->selectedRole]);
        } else {
            $this->user->syncRoles([]);
        }

        $this->dispatch('user-updated');

        // Redirect after update
        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        // No need to pass $user again, it's a public property
        return view('livewire.admin.users.edit');
    }
}
