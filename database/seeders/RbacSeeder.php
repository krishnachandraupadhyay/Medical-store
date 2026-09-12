<?php

namespace Database\Seeders;

use App\Services\RbacService;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Seed default permissions and system roles.
     */
    public function run(): void
    {
        app(RbacService::class)->seedDefaultSystemRoles();
    }
}
