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
            'view_any_sale', 'view_sale', 'create_sale', 'update_sale',
            // Purchases
            'view_any_purchase', 'view_purchase', 'create_purchase', 'update_purchase',
            // Delivery Notes
            'view_any_delivery_note', 'view_delivery_note', 'create_delivery_note', 'update_delivery_note',
            // Stock Entries
            'view_any_stock_entry', 'view_stock_entry', 'create_stock_entry', 'update_stock_entry',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::updateOrCreate(['name' => $permission]);
        }

        // Assign Permissions to Gudang
        $gudang->syncPermissions([
            'view_any_product', 'view_product', 'create_product', 'update_product',
            'view_any_purchase', 'view_purchase', 'create_purchase', 'update_purchase',
            'view_any_delivery_note', 'view_delivery_note', 'create_delivery_note', 'update_delivery_note',
            'view_any_stock_entry', 'view_stock_entry', 'create_stock_entry', 'update_stock_entry',
        ]);

        // Assign Permissions to Kasir
        $kasir->syncPermissions([
            'view_any_sale', 'view_sale', 'create_sale', 'update_sale',
            'view_any_customer', 'view_customer', 'create_customer', 'update_customer',
            'view_any_product', 'view_product',
        ]);
    }
}
