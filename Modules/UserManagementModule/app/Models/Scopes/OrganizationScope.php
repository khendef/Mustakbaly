<?php

namespace Modules\UserManagementModule\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Route;

class OrganizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
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
