<?php

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum; // Import Sanctum test helper

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Freshly migrate the central database schemas
    Artisan::call('migrate:fresh', ['--path' => 'database/migrations_central']);

    // Generate dynamic tenant IDs to avoid file locking conflicts
    $this->tenantId = 'company_' . uniqid();
    $this->tenantDomain = $this->tenantId . '.localhost';

    $this->tenant = Tenant::create(['id' => $this->tenantId]);
    $this->tenant->domains()->create(['domain' => $this->tenantDomain]);

    // Ensure central user and token configurations are reloaded fresh
    Artisan::call('optimize:clear');
});

test('it can create an invoice via api inside tenant context', function () {
    // Create a global user inside central context to act as the sender
    $user = User::create([
        'name' => 'Invoice Creator',
        'email' => 'creator@saas.com',
        'password' => bcrypt('password'),
    ]);

    // Authenticate the user via Sanctum for this specific test request
    Sanctum::actingAs($user, ['*']);

    // Send a POST request to the dynamic tenant domain
    $response = $this->json('POST', "http://{$this->tenantDomain}/api/invoices", [
        'invoice_number' => 'FV/2026/09/001',
        'customer_name' => 'Test Client Sp. z o.o.',
        'customer_tax_id' => 'PL1234567890',
        'net_amount' => 1000.00,
        'vat_amount' => 230.00,
        'gross_amount' => 1230.00,
        'due_date' => '2026-10-13',
    ]);

    // Assert a professional, multi-nested JSON response structure
    $response->assertStatus(201)
        ->assertJson([
            'status' => 'success',
            'message' => 'Invoice created successfully inside tenant database!',
            'data' => [
                'invoice_number' => 'FV/2026/09/001',
                'finances' => [
                    'net_amount' => '1000.00 EUR',
                    'gross_amount' => '1230.00 EUR',
                ]
            ]
        ]);

    // Verify raw data exists inside the isolated database schema
    $this->tenant->run(function () {
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'FV/2026/09/001',
            'customer_name' => 'Test Client Sp. z o.o.',
        ]);
    });
});

test('it can fetch invoices list from tenant context', function () {
    // Seed an invoice record directly into the tenant's isolated storage context
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

    // Create a global user inside central context to authorize access
    $user = User::create([
        'name' => 'Invoice Viewer',
        'email' => 'viewer@saas.com',
        'password' => bcrypt('password'),
    ]);

    // Authenticate the user via Sanctum before requesting protected data
    Sanctum::actingAs($user, ['*']);

    // Request the invoices index endpoint via the tenant domain
    $response = $this->json('GET', "http://{$this->tenantDomain}/api/invoices");

    // Assert that the resource collection formatting fits our resource expectations
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data') // InvoiceResource wraps the array inside 'data' root key
        ->assertJsonFragment([
            'invoice_number' => 'FV/2026/09/002',
            'net_amount' => '500.00 EUR'
        ]);
});
