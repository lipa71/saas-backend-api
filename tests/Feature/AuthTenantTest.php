<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->artisan('route:clear');

    // SECURITY SHIELD: Raw SQL drop hook executed via ROOT to completely smash prior ghost databases
    try {
        DB::connection('mysql')->statement('DROP DATABASE IF EXISTS tenantcompany_auth_test');
    } catch (\Exception $e) {}

    // Clean up landlord table matrices before starting the iteration
    Company::on('mysql')->where('id', 'company_auth_test')->delete();
    User::on('mysql')->where('email', 'nonexistent.worker@saas.com')->delete();
});

/**
 * CLEANUP HOOK: Hard purge the generated schema files directly inside the database server post-execution
 */
afterEach(function () {
    if (tenancy()->initialized) {
        tenancy()->end();
    }

    try {
        DB::connection('mysql')->statement('DROP DATABASE IF EXISTS tenantcompany_auth_test');
    } catch (\Exception $e) {}

    Company::on('mysql')->where('id', 'company_auth_test')->delete();
    User::on('mysql')->where('email', 'nonexistent.worker@saas.com')->delete();
});

test('workspace employee cannot log in with invalid credentials', function () {
    // 1. IDENTITY blueprinted directly inside the central landlord environment context
    $company = Company::on('mysql')->create([
        'id' => 'company_auth_test',
        'name' => 'Auth Validation Corporate',
        'email' => 'auth@saasplatform.com',
        'subscription_plan' => 'enterprise'
    ]);

    // 2. HARDENING MIGRATIONS: Force system to execute global tenant schema provisioning
    $this->artisan('tenants:migrate');

    // 3. LOGICAL VERIFICATION: Ensure fake credentials return false at core framework authentication layers
    $loginAttempt = auth()->attempt([
        'email' => 'nonexistent.worker@saas.com',
        'password' => 'wrong-password'
    ]);

    // 4. ABSOLUTE EXPECTATION: The framework authentication driver must strictly reject the validation vector
    expect($loginAttempt)->toBeFalse();
});
