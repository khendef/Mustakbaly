<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Application service provider for registering and bootstrapping application services.
 * Handles service container bindings and application-level configuration that runs on every request.
 */
class AppServiceProvider extends ServiceProvider
{
    // Methods

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
        //
    }
}
