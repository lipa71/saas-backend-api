<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionItem;
use Tests\TestCase;

class CentralSubscriptionArchitectureTest extends TestCase
{
    /**
     * Set up the test environment by explicitly running central migration schemas.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Dynamically execute the custom central migration path inside the isolated testing database
        $this->artisan('migrate:fresh', [
            '--path' => 'database/migrations_central',
            '--force' => true
        ]);
    }

    /**
     * Verify that the modern central landlord database relations map flawlessly under enterprise layouts.
     */
    public function test_central_user_can_own_companies_and_stripe_subscriptions_simultaneously(): void
    {
        // 1. Arrange: Create central landlord user context
        $user = User::create([
            'name' => 'Alexander Owner',
            'email' => 'owner@saas.com',
            'password' => bcrypt('password'),
        ]);

        // 2. Act: Establish corporate enterprise tenant mapping inside the tenants table
        $company = Company::create([
            'id' => 'test_company',
            'user_id' => $user->id,
            'name' => 'Test Company LLC',
            'email' => 'billing@testcompany.com',
            'subscription_plan' => 'enterprise',
        ]);

        // 3. Act: Inject mock Stripe production subscription data bound to the user
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'type' => 'main',
            'stripe_id' => 'sub_123456789_stripe',
            'stripe_status' => 'active',
            'stripe_price' => 'price_premium_yearly',
            'quantity' => 1,
            'ends_at' => now()->addYear(),
        ]);

        // 4. Act: Attach custom consumption metered billing items
        $item = SubscriptionItem::create([
            'subscription_id' => $subscription->id,
            'stripe_id' => 'si_987654321',
            'stripe_product' => 'prod_saas_core',
            'stripe_price' => 'price_premium_yearly',
            'quantity' => 1,
        ]);

        // 5. Assert: Flawless relational map verification down to the core fields
        $this->assertCount(1, $user->companies);
        $this->assertEquals('test_company', $user->companies->first()->id);

        $this->assertCount(1, $user->subscriptions);
        $this->assertEquals('active', $user->subscriptions->first()->stripe_status);

        $this->assertCount(1, $subscription->items);
        $this->assertEquals('prod_saas_core', $subscription->items->first()->stripe_product);

        $this->assertEquals($user->id, $company->user->id);
    }
}
