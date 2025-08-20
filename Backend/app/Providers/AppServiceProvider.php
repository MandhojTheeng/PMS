<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Clear cached roles/permissions during development
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Configure API rate limiting
        RateLimiter::for('api', function ($request) {
            $limit = env('API_RATE_LIMIT', 60);
            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });
    }
}