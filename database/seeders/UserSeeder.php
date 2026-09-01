<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@itchecklist.com',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        User::create([
            'name' => 'Admin IT',
            'email' => 'admin@itchecklist.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

    }
}
