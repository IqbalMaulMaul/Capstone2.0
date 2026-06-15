<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Owner Account
        User::firstOrCreate(
            ['email' => 'owner@hotel.com'],
            [
                'name' => 'Hotel Owner',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'is_active' => true,
            ]
        );

        // Kitchen Staff
        User::firstOrCreate(
            ['email' => 'kitchen@hotel.com'],
            [
                'name' => 'Head Chef',
                'password' => Hash::make('password'),
                'role' => 'kitchen',
                'is_active' => true,
            ]
        );

        // Finance Staff
        User::firstOrCreate(
            ['email' => 'finance@hotel.com'],
            [
                'name' => 'Finance Manager',
                'password' => Hash::make('password'),
                'role' => 'finance',
                'is_active' => true,
            ]
        );
    }
}
