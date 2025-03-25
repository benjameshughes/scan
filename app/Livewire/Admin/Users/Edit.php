<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    public User $user;
    public Collection $roles;
    public string $selectedRole;
    public Collection $currentRole;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->roles = Role::all()->pluck('name');
        $this->currentRole = $user->permissions;
    }

    private function ensureDefaultRole()
    {
        // Initialize as empty array if null
        if($this->selectedRole === null)
        {
            $this->selectedRole = $this->Roles['user'];
        }
    }

    public function updateRoles()
    {
        $this->ensureDefaultRole();

        $this->user->syncRoles($this->selectedRole);

        redirect()->route('admin.users.index');
    }

    public function render()
    {
        return view('livewire.admin.users.edit', [
            'user' => $this->user,
        ]);
    }
}
