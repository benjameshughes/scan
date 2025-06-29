<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserEdit extends Component
{
    use AuthorizesRequests;

    public User $user;

    public string $name = '';

    public string $email = '';

    public string $status = 'active';

    public array $selectedPermissions = [];

    public array $selectedRoles = [];

    // Permission groups for better organization
    public array $permissionGroups = [
        'Users' => [
            'view users' => 'View user list and profiles',
            'create users' => 'Create new users',
            'edit users' => 'Edit user information',
            'delete users' => 'Delete users',
        ],
        'Scans' => [
            'view scanner' => 'Access the barcode scanner',
            'view scans' => 'View scan history and details',
            'create scans' => 'Create new scans',
            'edit scans' => 'Edit scan information',
            'delete scans' => 'Delete scans',
            'sync scans' => 'Sync scans with Linnworks',
        ],
        'Products' => [
            'view products' => 'View product catalog',
            'create products' => 'Add new products',
            'edit products' => 'Edit product information',
            'delete products' => 'Delete products',
            'import products' => 'Import products from external sources',
        ],
        'Invitations' => [
            'view invites' => 'View user invitations',
            'create invites' => 'Send user invitations',
            'edit invites' => 'Edit pending invitations',
            'delete invites' => 'Cancel invitations',
        ],
    ];

    public function mount(User $user)
    {
        $this->authorize('update', $user);

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->status = $user->status ?? 'active';

        // Load current permissions and roles
        $this->selectedPermissions = $user->permissions->pluck('name')->toArray();
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    public function togglePermission(string $permission)
    {
        if (in_array($permission, $this->selectedPermissions)) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, [$permission]);
        } else {
            $this->selectedPermissions[] = $permission;
        }
    }

    public function toggleRole(string $role)
    {
        if (in_array($role, $this->selectedRoles)) {
            $this->selectedRoles = array_diff($this->selectedRoles, [$role]);
        } else {
            $this->selectedRoles[] = $role;
        }
    }

    public function save()
    {
        $this->authorize('update', $this->user);

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->user->id,
            'status' => 'required|in:active,inactive',
        ]);

        // Update user basic info
        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
        ]);

        // Only admins can modify permissions and roles
        if (auth()->user()->can('edit users')) {
            // Sync permissions
            $this->user->syncPermissions($this->selectedPermissions);

            // Sync roles
            $this->user->syncRoles($this->selectedRoles);
        }

        session()->flash('message', 'User updated successfully.');

        return redirect()->route('users.index');
    }

    public function cancel()
    {
        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.user-edit', [
            'allRoles' => Role::all(),
            'canEditPermissions' => auth()->user()->can('edit users'),
        ]);
    }
}
