<?php

namespace App\Livewire\Profile;

use Livewire\Component;

class UserRolesPermissions extends Component
{
    public function render()
    {
        return view('livewire.profile.user-roles-permissions', [
            'userRoles' => auth()->user()->roles->pluck('name')->toArray(),
            'userPermissions' => auth()->user()->getAllPermissions()->pluck('name')->toArray(),
        ]);
    }
}
