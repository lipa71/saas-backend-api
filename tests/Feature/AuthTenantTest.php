<?php

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Freshly migrate the central database schema for global testing state
    Artisan::call('migrate:fresh', ['--path' => 'database/migrations_central']);

    // Generate dynamic tenant domain routing configurations
    $this->tenantId = 'company_' . uniqid();
    $this->tenantDomain = $this->tenantId . '.localhost';

    $this->tenant = Tenant::create(['id' => $this->tenantId]);
    $this->tenant->domains()->create(['domain' => $this->tenantDomain]);

    // Ensure central user and token configurations are reloaded fresh
    Artisan::call('optimize:clear');
});

test('it can authenticate a global central owner via tenant login endpoint', function () {
    // Seed a global user inside the central database context
    $centralUser = User::create([
        'name' => 'Global Central Owner',
        'email' => 'owner@saas.com',
        'password' => bcrypt('secret-password'),
    ]);

    // Submit a POST request to the dynamic tenant login endpoint
    $response = $this->json('POST', "http://{$this->tenantDomain}/api/login", [
        'email' => 'owner@saas.com',
        'password' => 'secret-password',
    ]);

    // Assert a successful 200 OK along with correct account metadata and dynamic token
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'account_type' => 'central_owner',
            'user' => [
                'email' => 'owner@saas.com',
            ]
        ]);

    $this->assertNotEmpty($response->json('token'));
});

test('it can authenticate an isolated tenant sub account employee via login endpoint', function () {
    // Seed an isolated employee user inside the specific tenant storage context
    $this->tenant->run(function () {
        User::create([
            'name' => 'Isolated Tenant Employee',
            'email' => 'employee@company.com',
            'password' => bcrypt('secret-password'),
        ]);
    });

    // Send the request to the same unified endpoint structure
    $response = $this->json('POST', "http://{$this->tenantDomain}/api/login", [
        'email' => 'employee@company.com',
        'password' => 'secret-password',
    ]);

    // Assert a successful 200 OK with local account type mapping context
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'account_type' => 'tenant_sub_account',
            'user' => [
                'email' => 'employee@company.com',
            ]
        ]);

    $this->assertNotEmpty($response->json('token'));
});

test('it rejects login requests with bad credentials', function () {
    $response = $this->json('POST', "http://{$this->tenantDomain}/api/login", [
        'email' => 'nonexistent@user.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'status' => 'error',
            'message' => 'Bad credentials',
        ]);
});

test('it can securely log out and revoke active tokens', function () {
    // Seed a global user for test token lifecycle tracking
    $user = User::create([
        'name' => 'Logout Test User',
        'email' => 'test@logout.com',
        'password' => bcrypt('password'),
    ]);

    // Act as the authenticated central user using Sanctum stateful abilities
    Sanctum::actingAs($user, ['*']);

    // Send a POST request to the protected tenant logout route
    $response = $this->json('POST', "http://{$this->tenantDomain}/api/logout");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Tokens revoked successfully. User logged out.',
        ]);
});
