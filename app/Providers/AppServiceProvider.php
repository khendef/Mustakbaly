<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\OrganizationsModule\Models\Organization;
use Modules\UserManagementModule\Models\User;

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
        /**
          ---------------------------
         | Gate: manage-organization
          ---------------------------
         * Purpose: Verifies that a user with a 'manager' role
         *          actually has management rights over the requested organization
         * Logic: 1. Checks the 'organization_user' pivot table
         * 2. Filters by the current Organization ID
         * 3. Validates that the pivot 'role' column is set to 'manager'
         * @param  User  $user
         * @param  Organization  $organization
         * @return bool
         */
        Gate::define('manage-organization', function ($user, $organization) {

          return $user->organizations()
                        ->where('id', $organization->id)
                        ->wherePivot('role', 'manager')
                        ->exists();
        });

    }
}

