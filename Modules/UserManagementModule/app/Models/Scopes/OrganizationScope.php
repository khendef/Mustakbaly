<?php

namespace Modules\UserManagementModule\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Route;

class OrganizationScope implements Scope
{
    /**
     --------------------------
    | OrganizationScope
     --------------------------
     * Purpose: Ensures that every database query 
     *          automatically filters data to the current organization context
     * logic: When a Manager is logged in, this scope intercepts all Eloquent queries
     * for the model (Program, Course, Instructor, Student) and injects a 'where' clause 
     * based on the {organization} parameter in the URL
     * @param Builder $builder
     * @param Model $model
     */
    public function apply(Builder $builder, Model $model): void 
    {
        if(auth()->check())
        {
            $user = auth()->user();
        }

        if($user->hasRole('manager'))
        {
            $organization = Route::input('organization');
            $builder->where('organization_id',$organization->id);
        }
    }
}
