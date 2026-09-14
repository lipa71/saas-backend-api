<?php

it('returns a successful response for the application status endpoint', function () {
    // Test symuluje wejście na adres statusu API
    $response = $this->getJson('/api/v1/status');

    // Sprawdza, czy serwer odpowiedział statusem 200 OK
    $response->assertStatus(200);

    // Sprawdza, czy dane JSON są poprawne
    $response->assertJsonFragment([
        'status' => 'success',
        'message' => 'SaaS Backend API is running perfectly',
    ]);
});
