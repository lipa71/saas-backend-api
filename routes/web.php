<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api-test-direct', function () {
    return response()->json(['status' => 'direct-ok']);
});
