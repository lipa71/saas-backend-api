<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Central administration panel authentication endpoints
Route::prefix('api/central')->group(function () {
    Route::post('/login', [AuthController::class, 'loginCentral']);
});
