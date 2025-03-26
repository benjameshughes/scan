<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    public User $user;
    // Store all available role names
    public Collection $roles;
    // Store the name of the role currently selected in the form
    public string $selectedRole = ''; // Initialize as empty string

    public function mount(User $user)
    {
        $this->user = $user;
        // Get all role names. We only need the names for display and syncing.
        $this->roles = Role::pluck('name', 'name'); // Use pluck('name', 'name') for easier key access if needed

        // Get the name of the *first* role assigned to the user being edited.
        // Users might have multiple roles, but radio buttons usually select one.
        // If the user has no roles, `first()` will return null.
        $currentRoleName = $this->user->roles->first()?->name;

        // Set the selectedRole to the user's current role name if they have one.
        // Otherwise, it remains an empty string or you could set a default.
        if ($currentRoleName) {
            $this->selectedRole = $currentRoleName;
        }
        // Optional: Set a default if no role is assigned and you want one pre-selected
        // elseif ($this->roles->has('user')) { // Check if a 'user' role exists
        //     $this->selectedRole = 'user';
        // }
    }

    // Removed ensureDefaultRole - handled in mount and update

    public function updateRoles()
    {
        // Ensure a role is actually selected before syncing.
        // If selectedRole is empty, syncRoles([]) removes all roles.
        // You might want validation here to ensure a role is chosen.
        if (!empty($this->selectedRole)) {
            // syncRoles expects an array or collection of role names/IDs/models.
            // Passing a single name works too.
            $this->user->syncRoles([$this->selectedRole]);
        } else {
            // Handle the case where no role is selected, maybe remove all roles?
            $this->user->syncRoles([]);
            // Or add validation to prevent submitting without a role.
            // $this->validate(['selectedRole' => 'required']);
        }


        // Use session flash message for feedback instead of immediate redirect
        // This is generally better UX with Livewire.
        session()->flash('message', 'User roles updated successfully.');

        // Redirect after update
        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        // No need to pass $user again, it's a public property
        return view('livewire.admin.users.edit');
    }
}
