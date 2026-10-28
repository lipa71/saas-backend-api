<?php

it('returns a successful response for the application status endpoint', function () {
    // Test simulates an access to the API status address.
    $response = $this->getJson('/api/v1/status');

    // Checks whether the server responded with a 200 OK status
    $response->assertStatus(200);

    // Checks whether the JSON data is valid
    $response->assertJsonFragment([
        'status' => 'success',
        'message' => 'SaaS Backend API is running perfectly',
    ]);
});
