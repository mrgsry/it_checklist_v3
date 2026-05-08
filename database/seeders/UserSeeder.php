<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'superadmin@itchecklist.com',
            'password' => Hash::make('password123'),
            'role'     => 'superadmin',
        ]);

        User::create([
            'name'     => 'Admin IT',
            'email'    => 'admin@itchecklist.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Fahri Teknisi',
            'email'    => 'fahri@itchecklist.com',
            'password' => Hash::make('password123'),
            'role'     => 'user',
        ]);

        User::create([
            'name'     => 'Budi Teknisi',
            'email'    => 'budi@itchecklist.com',
            'password' => Hash::make('password123'),
            'role'     => 'user',
        ]);
    }
}