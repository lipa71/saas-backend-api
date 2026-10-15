<?php

use App\Models\Company;
use App\Models\Invoice;

beforeEach(function () {
    $this->artisan('route:clear');

    // Clean up any leftover testing workspaces before execution
    Company::on('mysql')->whereIn('id', ['company_1_test', 'company_2_test'])->get()->each(function ($company) {
        try {
            $company->delete();
        } catch (\Exception $e) {}
    });
});

/**
 * CLEANUP HOOK: Explicitly erase testing databases from the server post-test
 */
afterEach(function () {
    if (tenancy()->initialized) {
        tenancy()->end();
    }

    // Drop test domains kaskadowo to prevent memory locks
    Company::on('mysql')->whereIn('id', ['company_1_test', 'company_2_test'])->get()->each(function ($company) {
        try {
            $company->delete();
        } catch (\Exception $e) {}
    });
});

test('tenant 2 is strictly blocked from reading or intercepting tenant 1 commercial invoices', function () {
    // 1. INITIALIZE TENANT 1: Create the workspace header record
    $company1 = Company::on('mysql')->create([
        'id' => 'company_1_test',
        'name' => 'Company 1 Test space',
        'email' => 'finance1@company1test.com',
        'subscription_plan' => 'enterprise'
    ]);

    // Switch active execution focus directly to Tenant 1 database context
    tenancy()->initialize($company1);

    // Persist a highly private invoice inside Tenant 1 isolated schema
    $invoice1 = Invoice::create([
        'invoice_number' => 'INV-EU-2026-CONFIDENTIAL',
        'customer_name' => 'Secret International Client',
        'customer_vat_number' => 'PL9999999999',
        'due_date' => '2026-12-31',
        'net_amount' => 1000.00,
        'vat_amount' => 210.00,
        'gross_amount' => 1210.00,
    ]);

    // Verify the invoice exists flawlessly inside Tenant 1
    expect(Invoice::find($invoice1->id))->not->toBeNull();

    // Securely step out of Tenant 1 boundaries
    tenancy()->end();

    // 2. INITIALIZE TENANT 2: Create a completely separate corporate context
    $company2 = Company::on('mysql')->create([
        'id' => 'company_2_test',
        'name' => 'Company 2 Test space',
        'email' => 'finance2@company2test.com',
        'subscription_plan' => 'enterprise'
    ]);

    // Force system database context swap into the Tenant 2 schema framework
    tenancy()->initialize($company2);

    // 3. THE ISOLATION AUDIT: Attempt to locate Tenant 1's invoice ID within Tenant 2 connection context
    $breachedInvoice = Invoice::find($invoice1->id);

    // 4. ABSOLUTE EXPECTATION: The query must return NULL, proving database schemas are perfectly barricaded!
    expect($breachedInvoice)->toBeNull();
});
