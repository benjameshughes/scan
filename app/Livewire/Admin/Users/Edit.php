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

    // User permissions - array of permission => boolean
    public array $userPermissions = [];

    // All available permissions grouped by category
    public array $allPermissions = [];

    // User data
    public array $form = [
        'name' => '',
        'email' => '',
        'password' => '',
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

        // Load all available permissions grouped by category
        $this->allPermissions = [
            'users' => ['view users', 'create users', 'edit users', 'delete users'],
            'scans' => ['view scans', 'view scanner', 'create scans', 'edit scans', 'delete scans', 'sync scans'],
            'products' => ['view products', 'create products', 'edit products', 'delete products', 'import products', 'manage products', 'refill bays'],
            'invites' => ['view invites', 'create invites', 'edit invites', 'delete invites'],
            'notifications' => ['receive empty bay notifications'],
        ];

        // Load user's current permissions
        $this->userPermissions = [];
        foreach ($this->allPermissions as $category => $permissions) {
            foreach ($permissions as $permission) {
                $this->userPermissions[$permission] = $user->can($permission);
            }
        }
    }

    // Removed ensureDefaultRole - handled in mount and update

    public function updateUser()
    {
        try {
            $validated = $this->validate([
                'form.name' => 'required|string|max:255',
                'form.email' => 'required|email|max:255|unique:users,email,'.$this->user->id,
                'form.password' => 'nullable|string|min:6',
                'selectedRole' => 'nullable|string|exists:roles,name',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Please check the form for errors and try again.');
            throw $e;
        }

        $updateData = [
            'name' => $validated['form']['name'],
            'email' => $validated['form']['email'],
        ];

        if (! empty($validated['form']['password'])) {
            $updateData['password'] = Hash::make($validated['form']['password']);
        }

        $this->user->update($updateData);

        if (! empty($this->selectedRole)) {
            $this->user->syncRoles([$this->selectedRole]);
        } else {
            $this->user->syncRoles([]);
        }

        // Handle individual permissions for all users (including admins)
        foreach ($this->userPermissions as $permission => $hasPermission) {
            if ($hasPermission && ! $this->user->can($permission)) {
                $this->user->givePermissionTo($permission);
            } elseif (! $hasPermission && $this->user->can($permission)) {
                // Only revoke if permission was granted directly, not via role
                $this->user->revokePermissionTo($permission);
            }
        }

        $this->dispatch('user-updated');

        session()->flash('message', 'User updated successfully.');

        // Redirect after update
        return redirect()->route('users.index');
    }

    public function updatedSelectedRole($value)
    {
        // Admins can still have individual permissions configured
        // Remove the automatic permission setting to allow granular control
    }

    public function render()
    {
        // No need to pass $user again, it's a public property
        return view('livewire.admin.users.edit');
    }
}
