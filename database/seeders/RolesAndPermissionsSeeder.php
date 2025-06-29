<?php

namespace Database\Seeders;

use App\Models\User;
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

        // Create all permissions
        foreach (array_merge($userPermissions, $scanPermissions, $productPermissions, $invitePermissions) as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create Roles
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo(Permission::all());

        // Create User Role
        $userRole = Role::findOrCreate('user');
        $userRole->givePermissionTo([
            'view scanner',
            'create scans',
            'view scans',
            'view products',
        ]);

        // Assign admin role to specific user if exists
        $adminUser = User::where('email', 'ben@app.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('admin');
        }

        // Assign random roles to other users
        $users = User::where('email', '!=', 'ben@app.com')->get();
        foreach ($users as $user) {
            $role = fake()->randomElement(['admin', 'user', 'user', 'user']); // 75% chance user, 25% admin
            $user->assignRole($role);
        }

    }
}
