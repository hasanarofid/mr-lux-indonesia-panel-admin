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
        $superAdmin = Role::updateOrCreate(['name' => 'super_admin']);
        $gudang = Role::updateOrCreate(['name' => 'gudang']);
        $kasir = Role::updateOrCreate(['name' => 'kasir']);

        // Define Permissions
        $permissions = [
            // Products
            'view_any_product', 'view_product', 'create_product', 'update_product',
            // Customers
            'view_any_customer', 'view_customer', 'create_customer', 'update_customer',
            // Sales
            'view_any_sale', 'view_sale', 'create_sale', 'update_sale', 'delete_sale', 'delete_any_sale',
            // Purchases
            'view_any_purchase', 'view_purchase', 'create_purchase', 'update_purchase',
            // Delivery Notes
            'view_any_delivery_note', 'view_delivery_note', 'create_delivery_note', 'update_delivery_note',
            'view_any_automatic::delivery::note', 'view_automatic::delivery::note', 'create_automatic::delivery::note', 'update_automatic::delivery::note',
            'view_any_manual::delivery::note', 'view_manual::delivery::note', 'create_manual::delivery::note', 'update_manual::delivery::note',
            'view_any_custom_delivery_note', 'view_custom_delivery_note', 'create_custom_delivery_note', 'update_custom_delivery_note',
            // Stock Entries (Mutasi) - Using policy format stock::entry
            'view_any_stock::entry', 'view_stock::entry', 'create_stock::entry', 'update_stock::entry', 'delete_stock::entry', 'delete_any_stock::entry',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::updateOrCreate(['name' => $permission]);
        }

        // Assign Permissions to Gudang (Hanya Mutasi)
        $gudang->syncPermissions([
            'view_any_product', 'view_product', // Required to see products for mutation
            'view_any_stock::entry', 'view_stock::entry', 'create_stock::entry', 'update_stock::entry', 'delete_stock::entry',
        ]);

        // Assign Permissions to Kasir (Menu Penjualan & Surat Jalan)
        $kasir->syncPermissions([
            'view_any_product', 'view_product', // Required for sales
            'view_any_customer', 'view_customer', // Required for sales
            'view_any_sale', 'view_sale', 'create_sale', 'update_sale', 'delete_sale',
            'view_any_delivery_note', 'view_delivery_note', 'create_delivery_note', 'update_delivery_note',
            'view_any_automatic::delivery::note', 'view_automatic::delivery::note', 'create_automatic::delivery::note', 'update_automatic::delivery::note',
            'view_any_manual::delivery::note', 'view_manual::delivery::note', 'create_manual::delivery::note', 'update_manual::delivery::note',
        ]);
    }
}
