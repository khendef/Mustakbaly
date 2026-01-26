<?php

namespace Modules\UserManagementModule\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CourseAccessScope implements Scope
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
        if($user->hasRole('instructor'))
        {
            $builder->whereRaw("id IN
            (SELECT course_id FROM course_instructor WHERE user_id=?)
            ",[$user->id]);
        }
        elseif($user->hasRole('student')){
            $builder->whereRaw("id IN
            (SELECT course_id FROM enrollment WHERE user_id=?)
            ",[$user->id]);
        }
    }
}
