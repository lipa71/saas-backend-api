<?php

namespace Database\Seeders;

use App\Models\VatRate;
use Illuminate\Database\Seeder;

class TenantVatRatesSeeder extends Seeder
{
    /**
     * Run the database seeds for the official European VAT dictionary.
     */
    public function run(): void
    {
        $vatRates = [
            [
                'name' => 'Standard EU VAT Rate (21%)',
                'rate' => 21.00,
                'is_reverse_charge' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Reduced EU VAT Rate (8%)',
                'rate' => 8.00,
                'is_reverse_charge' => false,
                'is_active' => true,
            ],
            [
                'name' => 'EU Cross-Border Reverse Charge (0%)',
                'rate' => 0.00,
                'is_reverse_charge' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Exempt / Tax-Free (0%)',
                'rate' => 0.00,
                'is_reverse_charge' => false,
                'is_active' => true,
            ],
        ];

        foreach ($vatRates as $rateData) {
            VatRate::firstOrCreate(
                ['name' => $rateData['name']],
                $rateData
            );
        }
    }
}
