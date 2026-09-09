<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:super-admin 
                            {--name= : Name of the Super Administrator}
                            {--email= : Email address of the Super Administrator}
                            {--password= : Password for the Super Administrator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Super Administrator account without exposing public registration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('--- Medical Store Management System: Super Admin Setup ---');

        $name = $this->option('name') ?: $this->ask('Super Admin Full Name', 'System Administrator');
        $email = $this->option('email') ?: $this->ask('Super Admin Email Address');
        $password = $this->option('password') ?: $this->secret('Super Admin Password (min 8 characters)');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
            'store_id' => null,
            'email_verified_at' => now(),
        ]);

        $this->info("✓ Super Administrator [{$admin->email}] created successfully!");
        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Status', 'Store Isolation'],
            [[
                $admin->id,
                $admin->name,
                $admin->email,
                $admin->role->value,
                $admin->is_active ? 'Active' : 'Inactive',
                'System-Level (Global)',
            ]]
        );

        return self::SUCCESS;
    }
}
