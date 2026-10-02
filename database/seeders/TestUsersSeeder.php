<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds for operational test accounts inside the tenant context.
     */
    public function run(): void
    {
        // 1. Create a clean Manager Account (Full operational CRUD authority)
        $manager = User::firstOrCreate(
            ['email' => 'manager@companya.com'],
            [
                'name' => 'John Manager',
                'password' => bcrypt('password'),
            ]
        );
        $manager->assignRole('manager');

        // 2. Create a clean Accountant Account (Create & View authority, No Delete)
        $accountant = User::firstOrCreate(
            ['email' => 'accountant@companya.com'],
            [
                'name' => 'Mark Accountant',
                'password' => bcrypt('password'),
            ]
        );
        $accountant->assignRole('accountant');

        // 3. Create a clean Viewer Account (Strictly Read-Only podgląd)
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@companya.com'],
            [
                'name' => 'Jane Viewer',
                'password' => bcrypt('password'),
            ]
        );
        $viewer->assignRole('viewer');

        // 4. Create the shadow owner copy matching the corporate platform subscription context
        User::firstOrCreate(
            ['email' => 'owner@saas.com'],
            [
                'name' => 'Tenant Owner Shadow',
                'password' => bcrypt('unusable_local_password_fallback'), // Verified centrally by AuthController
            ]
        );

    }
}
