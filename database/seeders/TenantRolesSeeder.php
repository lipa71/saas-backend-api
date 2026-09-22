<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class TenantRolesSeeder extends Seeder
{
    /**
     * Run the database seeds inside the isolated tenant context.
     */
    public function run(): void
    {
        // Define standard corporate roles for the enterprise B2B SaaS platform
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
            ],
            [
                'name' => 'accountant',
                'display_name' => 'Accountant',
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Read-Only Viewer',
            ],
        ];

        // Seed or update roles safely without duplicating raw SQL rows
        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['display_name' => $role['display_name']]
            );
        }
    }
}
