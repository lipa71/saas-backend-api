<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

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

        // UNIFIED: Define a rigid enterprise rate limiter for internal tenant API resources
        RateLimiter::for('tenant_api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
