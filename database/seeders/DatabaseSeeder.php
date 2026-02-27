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

        // Gudang User
        $gudang = User::updateOrCreate(
            ['email' => 'gudang@lux.id'],
            [
                'name' => 'Staff Gudang',
                'password' => bcrypt('password'),
            ]
        );
        $gudang->assignRole('gudang');

        // Kasir User
        $kasir = User::updateOrCreate(
            ['email' => 'kasir@lux.id'],
            [
                'name' => 'Staff Kasir',
                'password' => bcrypt('password'),
            ]
        );
        $kasir->assignRole('kasir');

        // Products
        $products = [
            // EPOXY
            ['name' => 'Epoxy - Kaleng 1kg', 'sku' => 'k-1', 'category' => 'EPOXY', 'uom' => 'SET', 'isi' => 12, 'price' => 0, 'price_per_carton' => 0, 'stock' => 10],
            ['name' => 'Epoxy - Kaleng 1/2kg', 'sku' => 'k-1/2', 'category' => 'EPOXY', 'uom' => 'SET', 'isi' => 24, 'price' => 54000, 'price_per_carton' => 1296000, 'stock' => 25],
            ['name' => 'Epoxy - Kaleng 1/4kg', 'sku' => 'k-1/4', 'category' => 'EPOXY', 'uom' => 'SET', 'isi' => 48, 'price' => 0, 'price_per_carton' => 0, 'stock' => 30],
            ['name' => 'Epoxy Botol 1kg', 'sku' => 'b-1', 'category' => 'EPOXY', 'uom' => 'SET', 'isi' => 10, 'price' => 0, 'price_per_carton' => 0, 'stock' => 15],
            ['name' => 'Epoxy Botol 1/2kg', 'sku' => 'b-1/2', 'category' => 'EPOXY', 'uom' => 'SET', 'isi' => 15, 'price' => 0, 'price_per_carton' => 0, 'stock' => 20],
            
            // POLYURETHANE (PU)
            ['name' => 'Epoxy PU - Botol 1kg', 'sku' => 'PU-1', 'category' => 'POLYURETHANE (PU)', 'uom' => 'PCS', 'isi' => 20, 'price' => 67000, 'price_per_carton' => 1340000, 'stock' => 40],
            ['name' => 'Epoxy PU - Botol 1/2kg', 'sku' => 'PU-1/2', 'category' => 'POLYURETHANE (PU)', 'uom' => 'PCS', 'isi' => 30, 'price' => 34500, 'price_per_carton' => 1035000, 'stock' => 55],
            ['name' => 'Epoxy PU - Botol 1/4kg', 'sku' => 'PU-1/4', 'category' => 'POLYURETHANE (PU)', 'uom' => 'PCS', 'isi' => 96, 'price' => 16500, 'price_per_carton' => 1584000, 'stock' => 100],
            
            // NON SAG
            ['name' => 'Non Sag - Kaleng 1kg', 'sku' => 'NS-1', 'category' => 'NON SAG', 'uom' => 'SET', 'isi' => 8, 'price' => 175000, 'price_per_carton' => 1400000, 'stock' => 12],
            ['name' => 'Non Sag - Kaleng 1/2kg', 'sku' => 'NS-1/2', 'category' => 'NON SAG', 'uom' => 'SET', 'isi' => 12, 'price' => 97500, 'price_per_carton' => 1170000, 'stock' => 18],
            
            // ALIFATIK
            ['name' => 'Alifatik - Botol 600gr', 'sku' => 'AL-600', 'category' => 'ALIFATIK', 'uom' => 'PCS', 'isi' => 24, 'price' => 17500, 'price_per_carton' => 420000, 'stock' => 24],
            
            // ALTECO
            ['name' => 'Alteco - Pcs 50ml', 'sku' => 'Alt-50', 'category' => 'ALTECO', 'uom' => 'PCS', 'isi' => 50, 'price' => 6250, 'price_per_carton' => 312500, 'stock' => 50],
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
