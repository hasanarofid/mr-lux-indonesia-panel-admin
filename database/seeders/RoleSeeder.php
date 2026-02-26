<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        Role::updateOrCreate(['name' => 'super_admin']);
        Role::updateOrCreate(['name' => 'gudang']);
        Role::updateOrCreate(['name' => 'kasir']);
    }
}
