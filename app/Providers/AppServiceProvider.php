<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        // Grant absolute access to the tenant owner before any other permission checks
        Gate::before(function (User $user, string $ability) {
            if ($user->isTenantOwner()) {
                return true;
            }

            return null;
        });
    }
}
