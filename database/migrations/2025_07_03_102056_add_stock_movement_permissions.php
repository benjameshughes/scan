<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear the cache to ensure fresh permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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
        ];

        // Location management permissions
        $locationPermissions = [
            'view locations',              // View location dashboard
            'create locations',            // Create new locations
            'edit locations',              // Edit location details
            'delete locations',            // Delete locations
            'activate locations',          // Activate/deactivate locations
            'manage locations',            // Manage stock locations (high level)
        ];

        // Create all new permissions first
        foreach (array_merge($stockMovementPermissions, $locationPermissions) as $permission) {
            Permission::findOrCreate($permission);
        }

        // Ensure basic permissions exist (they should from the seeder)
        $basicPermissions = [
            'view scanner', 'create scans', 'view scans', 'view products', 'refill bays',
            'view users', 'create users', 'edit users',
        ];

        foreach ($basicPermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create new roles with appropriate permissions
        $this->createStockManagerRole($stockMovementPermissions, $locationPermissions);
        $this->createSupervisorRole($stockMovementPermissions, $locationPermissions);
        $this->createWarehouseWorkerRole($stockMovementPermissions, $locationPermissions);

        // Update admin role to have all new permissions (if it exists)
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_merge($stockMovementPermissions, $locationPermissions));
        }
    }

    /**
     * Create Stock Manager Role
     */
    private function createStockManagerRole(array $stockMovementPermissions, array $locationPermissions): void
    {
        $stockManagerRole = Role::findOrCreate('stock_manager');

        $stockManagerPermissions = [
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
        ];

        $stockManagerRole->syncPermissions($stockManagerPermissions);
    }

    /**
     * Create Supervisor Role
     */
    private function createSupervisorRole(array $stockMovementPermissions, array $locationPermissions): void
    {
        $supervisorRole = Role::findOrCreate('supervisor');

        $supervisorPermissions = [
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
        ];

        $supervisorRole->syncPermissions($supervisorPermissions);
    }

    /**
     * Create Warehouse Worker Role
     */
    private function createWarehouseWorkerRole(array $stockMovementPermissions, array $locationPermissions): void
    {
        $warehouseWorkerRole = Role::findOrCreate('warehouse_worker');

        $warehouseWorkerPermissions = [
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
        ];

        $warehouseWorkerRole->syncPermissions($warehouseWorkerPermissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear the cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Remove the new roles
        Role::where('name', 'stock_manager')->delete();
        Role::where('name', 'supervisor')->delete();
        Role::where('name', 'warehouse_worker')->delete();

        // Remove the new permissions
        $permissionsToRemove = [
            'view stock movements',
            'create stock movements',
            'edit stock movements',
            'delete stock movements',
            'approve stock movements',
            'bulk stock movements',
            'import stock movements',
            'export stock movements',
            'view stock reports',
            'view locations',
            'create locations',
            'edit locations',
            'delete locations',
            'activate locations',
            'manage locations',
        ];

        Permission::whereIn('name', $permissionsToRemove)->delete();
    }
};
