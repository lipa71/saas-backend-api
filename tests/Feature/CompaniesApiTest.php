<?php

it('denies access to companies list for unauthenticated users', function () {
    // Próbujemy wejść na listę firm bez logowania i bez tokenu Bearer
    $response = $this->getJson('/api/companies');

    // Sprawdzamy, czy system prawidłowo odciął dostęp (status 401 Unauthorized)
    $response->assertStatus(401);

    // Zmieniamy to pole, aby pasowało do standardu Laravela w testach
    $response->assertJsonFragment([
        'message' => 'Unauthenticated.',
    ]);
});
