<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminGudangRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create the admin_gudang role
        $role = Role::updateOrCreate(['name' => 'admin_gudang']);

        // First, ensure all essential permissions exist by running RoleSeeder (or just creating them here)
        // Since RoleResource has view_any_role, create_role, etc., we need to ensure those exist.
        
        $essentialPermissions = [
            'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user', 'delete_any_user',
            'view_any_role', 'view_role', 'create_role', 'update_role', 'delete_role', 'delete_any_role',
            'view_any_activity', 'view_any_sales::report',
        ];

        foreach ($essentialPermissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }

        // Assign ALL permissions to admin_gudang
        $allPermissions = Permission::all();
        $role->syncPermissions($allPermissions);
    }
}
