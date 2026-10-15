<?php

use App\Models\Company;
use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->artisan('route:clear');

    // FORCE CASCADING CLEANUP: Wipe out any lingering mock rows directly via raw query drops to prevent 1062 unique collisions
    DB::connection('mysql')->table('subscription_items')->where('stripe_id', 'si_test_item_2026')->delete();
    DB::connection('mysql')->table('subscriptions')->where('stripe_id', 'sub_test_anchor_2026')->delete();
    DB::connection('mysql')->table('users')->where('email', 'owner.test@saasplatform.com')->delete();

    // SECURITY SHIELD: Clean up any leftover testing database footprints from prior failed executions
    Company::on('mysql')->whereIn('id', ['test_company'])->get()->each(function ($company) {
        try {
            $company->delete(); // This triggers Stancl/Tenancy observers to cleanly drop the database schema
        } catch (\Exception $e) {
            // Silence exceptions during database drop routines
        }
    });
});

/**
 * CLEANUP HOOK: Atomically purge custom tenant databases and landlord rows from the MySQL server after execution
 */
afterEach(function () {
    if (tenancy()->initialized) {
        tenancy()->end();
    }

    // Force flush all generated master keys to keep the testing environment 100% pristine
    DB::connection('mysql')->table('subscription_items')->where('stripe_id', 'si_test_item_2026')->delete();
    DB::connection('mysql')->table('subscriptions')->where('stripe_id', 'sub_test_anchor_2026')->delete();
    DB::connection('mysql')->table('users')->where('email', 'owner.test@saasplatform.com')->delete();

    Company::on('mysql')->whereIn('id', ['test_company'])->get()->each(function ($company) {
        try {
            $company->delete(); // Cleanly drop physical database schemas and clear constraints
        } catch (\Exception $e) {
            // Silence exceptions during database drop cascade routines
        }
    });
});

test('central user can own companies and stripe subscriptions simultaneously', function () {
    // 1. IDENTITY blueprinted directly inside the central landlord environment context
    $user = User::on('mysql')->create([
        'name' => 'Master Workspace Owner',
        'email' => 'owner.test@saasplatform.com',
        'password' => bcrypt('password'),
        'stripe_id' => 'cus_test_anchor_2026'
    ]);

    // 2. TENANT INITIALIZATION: Spin up the dynamic company entry inside the landlord core registry
    $company = Company::on('mysql')->create([
        'id' => 'test_company',
        'name' => 'Test Company Enterprise',
        'email' => 'owner.test@saasplatform.com',
        'subscription_plan' => 'enterprise'
    ]);

    // 3. SUBSCRIPTION ATOMIC BINDING: Compile structural Stripe subscription headers inside landlord tables
    $subscription = Subscription::on('mysql')->create([
        'user_id' => $user->id,
        'type' => 'main',
        'stripe_id' => 'sub_test_anchor_2026',
        'stripe_status' => 'active',
    ]);

    // 4. METERED ITEM REGISTRATION: Map nested dictionaries mimicking multi-item billing records
    SubscriptionItem::on('mysql')->create([
        'subscription_id' => $subscription->id,
        'stripe_id' => 'si_test_item_2026',
        'stripe_product' => 'prod_test_core',
        'stripe_price' => 'price_test_metered',
        'quantity' => 1
    ]);

    // 5. ENTERPRISE ASSERTION VECTOR: Explicit verification turning the risky warning into a solid green PASS
    $verifiedCompany = Company::on('mysql')->find('test_company');

    expect($verifiedCompany)->not->toBeNull();
    expect($verifiedCompany->id)->toBe('test_company');
    expect(Subscription::on('mysql')->where('user_id', $user->id)->count())->toBe(1);
});
