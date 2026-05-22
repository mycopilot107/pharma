<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'fleet_test@gmail.com'],
            [
                'name' => 'Platform Super Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::SuperAdmin,
                'company_id' => null,
                'is_active' => true,
            ],
        );
    }
}
