<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles
        $this->call(RoleSeeder::class);

        // Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@lux.id'],
            [
                'name' => 'Admin Lux',
                'password' => bcrypt('password'),
            ]
        );

        $admin->assignRole('super_admin');

        // Products
        $products = [
            ['name' => 'Clear Coat Barrel', 'sku' => 'CC-BRL', 'uom' => 'PCS', 'price' => 5000000, 'stock' => 10],
            ['name' => 'Epoxy Adhesive Set', 'sku' => 'EPX-SET', 'uom' => 'SET', 'price' => 250000, 'stock' => 50],
            ['name' => 'Hardener 1L', 'sku' => 'HRD-1L', 'uom' => 'PCS', 'price' => 150000, 'stock' => 100],
            ['name' => 'Thinner Polyurethane', 'sku' => 'THN-PU', 'uom' => 'DUS', 'price' => 450000, 'stock' => 20],
            ['name' => 'Color Pigment Blue', 'sku' => 'PGM-BLU', 'uom' => 'PAK', 'price' => 85000, 'stock' => 40],
        ];

        foreach ($products as $product) {
            \App\Models\Product::updateOrCreate(['sku' => $product['sku']], $product);
        }

        // Customers
        $customers = [
            ['name' => 'PT Bangun Sejahtera', 'group' => 'PPN', 'phone' => '08123456789', 'address' => 'Semarang Central'],
            ['name' => 'Toko Cat Jaya', 'group' => 'NON-PPN', 'phone' => '087766554433', 'address' => 'Kendal Jaya'],
            ['name' => 'CV Interior Mewah', 'group' => 'PPN', 'phone' => '08987654321', 'address' => 'Demak Asri'],
        ];

        foreach ($customers as $customer) {
            \App\Models\Customer::updateOrCreate(['name' => $customer['name']], $customer);
        }
    }
}
