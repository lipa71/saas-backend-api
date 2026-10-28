<?php

it('denies access to companies list for unauthenticated users', function () {
    // Attempt to access the companies list without logging in and without a Bearer token
    $response = $this->getJson('/api/companies');

    // Verify that the system correctly restricts access (401 Unauthorized status)
    $response->assertStatus(401);

    // Assert that the response matches the default Laravel unauthenticated JSON structure
    $response->assertJsonFragment([
        'message' => 'Unauthenticated.',
    ]);
});
