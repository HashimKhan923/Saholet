<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gharfix.pk'],
            [
                'name' => 'Platform Admin',
                'phone' => '+920000000000',
                'role' => User::ROLE_ADMIN,
                'password' => 'password', // hashed via model cast — CHANGE THIS
                'email_verified_at' => now(),
            ]
        );
    }
}