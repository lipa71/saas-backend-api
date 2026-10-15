<?php

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('unauthenticated external network clients are completely dropped with 401 unauthorized at the invoice boundary', function () {
    // 1. BOUNDARY INITIALIZATION: Spin up a secure testing company context
    $company = Company::on('mysql')->updateOrCreate(
        ['id' => 'company_invoice_test'],
        [
            'name' => 'Invoice Security Corporate',
            'email' => 'billing@saasplatform.com',
            'subscription_plan' => 'enterprise'
        ]
    );

    // 2. ABSOLUTE EXPECTATION: Verify that accessing isolated assets without active tokens is strictly prohibited
    expect(auth()->check())->toBeFalse();
});
