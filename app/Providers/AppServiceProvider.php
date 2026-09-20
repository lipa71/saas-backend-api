<?php

namespace App\Providers;

use App\Models\CentralPersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force Sanctum to use our central database model for token validation
        Sanctum::usePersonalAccessTokenModel(CentralPersonalAccessToken::class);
    }
}
