<?php

use App\Models\Company;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->artisan('route:clear');

    // FORCE RAW SQL PURGE: Bezwzględne wyczyszczenie bazy testowej bezpośrednio w silniku MySQL
    try {
        DB::connection('mysql')->statement('DROP DATABASE IF EXISTS tenantcompany_auth_test');
    } catch (\Exception $e) {}

    // Security core cleanup inside landlord table
    Company::on('mysql')->whereIn('id', ['company_auth_test'])->delete();
});

/**
 * CLEANUP HOOK: Hard purge the generated schema files directly inside the database server
 */
afterEach(function () {
    if (tenancy()->initialized) {
        tenancy()->end();
    }

    try {
        DB::connection('mysql')->statement('DROP DATABASE IF EXISTS tenantcompany_auth_test');
    } catch (\Exception $e) {}

    Company::on('mysql')->whereIn('id', ['company_auth_test'])->delete();
});

test('workspace employee cannot log in with invalid credentials', function () {
    // 1. BOUNDARY INITIALIZATION: Spin up a secure testing company context
    $company = Company::on('mysql')->updateOrCreate(
        ['id' => 'company_auth_test'],
        [
            'name' => 'Auth Validation Corporate',
            'email' => 'auth@saasplatform.com',
            'subscription_plan' => 'enterprise'
        ]
    );

    // 2. LOGICAL VERIFICATION: Ensure fake credentials return false at core framework authentication layers
    $loginAttempt = auth()->attempt([
        'email' => 'nonexistent.worker@saas.com',
        'password' => 'wrong-password'
    ]);

    // 3. ABSOLUTE EXPECTATION: The framework authentication driver must strictly reject the validation vector
    expect($loginAttempt)->toBeFalse();
});
