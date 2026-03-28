<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SpecificUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Akhsan',
                'email' => 'akhsan@mrluxindonesia.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Mas Ryan',
                'email' => 'ryan@mrluxindonesia.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Ima',
                'email' => 'ima@mrluxindonesia.com',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ],
            [
                'name' => 'Kisno',
                'email' => 'kisno@mrluxindonesia.com',
                'password' => Hash::make('password'),
                'role' => 'gudang',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                ]
            );

            // Assign role using Spatie Permission (consistent with DatabaseSeeder)
            $user->assignRole($userData['role']);
        }
    }
}
