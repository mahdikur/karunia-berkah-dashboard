<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Superadmin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('superadmin'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        // Create Staff
        User::create([
            'name' => 'Staff Gudang',
            'email' => 'staff@gmail.com',
            'password' => Hash::make('staff'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        // Additional Staff
        User::create([
            'name' => 'Budi Staff',
            'email' => 'budi@gmail.com',
            'password' => Hash::make('budi'),
            'role' => 'staff',
            'is_active' => true,
        ]);
    }
}
