<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Artisan::call('migrate:fresh', ['--path' => 'database/migrations_central']);

    $this->tenantId = 'company_' . uniqid();
    $this->tenantDomain = $this->tenantId . '.localhost';

    $this->tenant = Tenant::create(['id' => $this->tenantId]);
    $this->tenant->domains()->create(['domain' => $this->tenantDomain]);
});

test('it can create an invoice via api inside tenant context', function () {
    $response = $this->json('POST', "http://{$this->tenantDomain}/api/invoices", [
        'invoice_number' => 'FV/2026/09/001',
        'customer_name' => 'Test Client Sp. z o.o.',
        'customer_tax_id' => 'PL1234567890',
        'net_amount' => 1000.00,
        'vat_amount' => 230.00,
        'gross_amount' => 1230.00,
        'due_date' => '2026-10-13',
    ]);

    // DOPASOWANIE DO PRO-FORMATU Z KONTROLERA
    $response->assertStatus(201)
        ->assertJson([
            'status' => 'success',
            'message' => 'Invoice created successfully inside tenant database!',
        ]);

    $this->tenant->run(function () {
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'FV/2026/09/001',
            'customer_name' => 'Test Client Sp. z o.o.',
        ]);
    });
});

test('it can fetch invoices list from tenant context', function () {
    $this->tenant->run(function () {
        DB::table('invoices')->insert([
            'invoice_number' => 'FV/2026/09/002',
            'customer_name' => 'Another Client',
            'customer_tax_id' => 'PL0987654321',
            'net_amount' => 500.00,
            'vat_amount' => 115.00,
            'gross_amount' => 615.00,
            'status' => 'draft',
            'due_date' => '2026-09-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    });

    $response = $this->json('GET', "http://{$this->tenantDomain}/api/invoices");

    // WSKAZUJEMY, ŻE CHCEMY POLICZYĆ ELEMENTY WEWNĄTRZ PROPERTIES 'data'
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['invoice_number' => 'FV/2026/09/002']);
});
