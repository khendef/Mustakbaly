<?php

namespace Modules\UserManagementModule\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CourseAccessScope implements Scope
{
   /**
     |------------------------
     |  CourseAccessScope
     | ------------------------
     * Purpose: Enforces Ownership-Based Access Control (OBAC) at the query level
     * Note: This scope ensures that users cannot view or modify courses they are not officially linked to
     * Logic:
     * 1. For Instructors: Limits results to courses where the user is listed in 
     * the 'course_instructor' pivot table.
     * 2. For Students: Limits results to courses where the user has an active 
     * record in the 'enrollment' table.
     * 
    */
    public function apply(Builder $builder, Model $model): void 
    {
        if(!auth()->check())
        {
          return;  
        }
        $user = auth()->user();
        if($user->hasRole('instructor'))
        {
            $builder->whereRaw("id IN
            (SELECT course_id FROM course_instructor WHERE user_id=?)
            ",[$user->id]);
        }
        elseif ($user->hasRole('student')){
            $builder->whereRaw("id IN
            (SELECT course_id FROM enrollment WHERE user_id=?)
            ",[$user->id]);
        }
    }
}
