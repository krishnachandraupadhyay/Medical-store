<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'superadmin@gmail.com');
        $password = env('SUPER_ADMIN_PASSWORD', '12345678');
        $name = env('SUPER_ADMIN_NAME', 'Super Admin');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => UserRole::SUPER_ADMIN,
                'is_active' => true,
                'store_id' => null, // Super Admin is not tied to any store
                'email_verified_at' => now(),
            ]
        );
    }
}
