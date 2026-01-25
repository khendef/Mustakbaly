<?php

namespace App\Providers;

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
        Gate::define('manage-organization', function ($user, $organization) {

          return $user->organizations()
                        ->where('id', $organization->id)
                        ->wherePivot('role', 'manager')
                        ->exists();
        });

    }
}

