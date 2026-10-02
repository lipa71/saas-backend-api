<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the active tenant's operational database dictionary.
     */
    public function run(): void
    {
        // 1. Seed the default system roles
        $this->call(TenantRolesSeeder::class);

        // 2. Seed the official EU VAT rates dictionary in EUR
        $this->call(TenantVatRatesSeeder::class);
    }
}
