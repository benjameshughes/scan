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
            'manage products', // For admin product sync features
            'refill bays', // For warehouse workers to refill empty bays
        ];

        // Invite-related permissions
        $invitePermissions = [
            'view invites',
            'create invites',
            'edit invites',
            'delete invites',
        ];

        // Stock Movement permissions - separate from bay refill
        $stockMovementPermissions = [
            'view stock movements',        // View stock movement history/reports
            'create stock movements',      // Create manual stock movements
            'edit stock movements',        // Edit existing stock movements
            'delete stock movements',      // Delete stock movements
            'approve stock movements',     // Approve pending movements (supervisor level)
            'bulk stock movements',        // Perform bulk/batch movements
            'import stock movements',      // Import movements from CSV/files
            'export stock movements',      // Export movement data
            'view stock reports',          // View detailed stock movement reports
            'manage locations',            // Manage stock locations
        ];

        // Location management permissions
        $locationPermissions = [
            'view locations',              // View location dashboard
            'create locations',            // Create new locations
            'edit locations',              // Edit location details
            'delete locations',            // Delete locations
            'activate locations',          // Activate/deactivate locations
        ];

        // Notification-related permissions
        $notificationPermissions = [
            'receive empty bay notifications',
        ];

        // Create all permissions
        foreach (array_merge($userPermissions, $scanPermissions, $productPermissions, $invitePermissions, $stockMovementPermissions, $locationPermissions, $notificationPermissions) as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create Roles
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo(Permission::all());

        // Create User Role (Basic Scanner User)
        $userRole = Role::findOrCreate('user');
        $userRole->givePermissionTo([
            'view scanner',
            'create scans',
            'view scans',
            'view products',
        ]);

        // Create Stock Manager Role
        $stockManagerRole = Role::findOrCreate('stock_manager');
        $stockManagerRole->givePermissionTo([
            // Basic user permissions
            'view scanner',
            'create scans',
            'view scans',
            'view products',
            // Stock movement permissions
            'view stock movements',
            'create stock movements',
            'edit stock movements',
            'view stock reports',
            'bulk stock movements',
            'export stock movements',
            // Location permissions
            'view locations',
            'create locations',
            'edit locations',
            // Bay refill (keep existing)
            'refill bays',
        ]);

        // Create Supervisor Role
        $supervisorRole = Role::findOrCreate('supervisor');
        $supervisorRole->givePermissionTo([
            // All stock manager permissions
            'view scanner',
            'create scans',
            'view scans',
            'view products',
            'view stock movements',
            'create stock movements',
            'edit stock movements',
            'view stock reports',
            'bulk stock movements',
            'export stock movements',
            'view locations',
            'create locations',
            'edit locations',
            'refill bays',
            // Additional supervisor permissions
            'delete stock movements',
            'approve stock movements',
            'import stock movements',
            'manage locations',
            'activate locations',
            'delete locations',
            // User management
            'view users',
            'create users',
            'edit users',
        ]);

        // Create Warehouse Worker Role
        $warehouseWorkerRole = Role::findOrCreate('warehouse_worker');
        $warehouseWorkerRole->givePermissionTo([
            // Basic scanner permissions
            'view scanner',
            'create scans',
            'view scans',
            'view products',
            // Limited stock movement permissions
            'view stock movements',
            'create stock movements',
            'view locations',
            // Bay refill (keep existing)
            'refill bays',
        ]);

        // Assign admin role to specific user if exists
        $adminUser = User::where('email', 'ben@app.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('admin');
        }

        // Assign random roles to other users with realistic distribution
        $users = User::where('email', '!=', 'ben@app.com')->get();
        foreach ($users as $user) {
            $role = fake()->randomElement([
                'user',            // 40% - Basic scanner users
                'user',
                'user',
                'user',
                'warehouse_worker', // 30% - Warehouse workers
                'warehouse_worker',
                'warehouse_worker',
                'stock_manager',   // 20% - Stock managers
                'stock_manager',
                'supervisor',      // 10% - Supervisors
            ]);
            $user->assignRole($role);
        }

    }
}
