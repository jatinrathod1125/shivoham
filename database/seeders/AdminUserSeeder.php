<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Super Admin
        User::updateOrCreate(
            ['email' => 'admin@grocery.local'],
            [
                'name' => 'Store Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPER_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'phone' => '+1 (555) 019-2834',
                'email_verified_at' => now(),
            ]
        );

        // 2. Store Manager
        User::updateOrCreate(
            ['email' => 'manager@grocery.local'],
            [
                'name' => 'Store Manager',
                'password' => Hash::make('password'),
                'role' => User::ROLE_MANAGER,
                'status' => User::STATUS_ACTIVE,
                'phone' => '+1 (555) 019-2835',
                'email_verified_at' => now(),
            ]
        );

        // 3. Inventory Staff
        User::updateOrCreate(
            ['email' => 'staff@grocery.local'],
            [
                'name' => 'Inventory Staff',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STAFF,
                'status' => User::STATUS_ACTIVE,
                'phone' => '+1 (555) 019-2836',
                'email_verified_at' => now(),
            ]
        );
    }
}
