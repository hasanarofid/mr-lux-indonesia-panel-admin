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
            ['email' => 'admin@mrluxindonesia.com'],
            [
                'name' => 'Agung',
                'password' => bcrypt('password'),
            ]
        );

        $admin->assignRole('super_admin');

        // Gudang User
        $gudang = User::updateOrCreate(
            ['email' => 'gudang@mrluxindonesia.com'],
            [
                'name' => 'Staff Gudang',
                'password' => bcrypt('password'),
            ]
        );
        $gudang->assignRole('gudang');

        // Kasir User
        $kasir = User::updateOrCreate(
            ['email' => 'kasir@mrluxindonesia.com'],
            [
                'name' => 'Staff Kasir',
                'password' => bcrypt('password'),
            ]
        );
        $kasir->assignRole('kasir');

        // Products
        $this->call(ProductSeeder::class);

        // Customers
        $this->call(CustomerSeeder::class);
    }
}
