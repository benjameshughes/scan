<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // User-related permissions
        $userPermissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
        ];

        // Scan-related permissions
        $scanPermissions = [
            'view scans',
            'view scanner',
            'create scans',
            'edit scans',
            'delete scans',
            'sync scans',
        ];

        // Product-related permissions
        $productPermissions = [
            'view products',
            'create products',
            'edit products',
            'delete products',
            'import products',
        ];

        // Invite-related permissions
        $invitePermissions = [
            'view invites',
            'create invites',
            'edit invites',
            'delete invites',
        ];

        // Create Roles
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo(Permission::all());

        // Create User Role
        $userRole = Role::findOrCreate('user');
        $userRole->givePermissionTo([
            
        ]);

        // Assign admin role to user
        $user = User::find(1);
        if($user)
        {
            $user->assignRole('admin');
        }

    }
}
