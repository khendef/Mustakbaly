<?php

namespace Modules\UserManagementModule\Services\V1;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\UserManagementModule\Enums\UserRole;
use Modules\UserManagementModule\Models\User;

class InstructorService
{
    public function list($filters, int $perPage=15)
    {
        $instructors = User::whereHas('instructorProfile')
                    ->with('instructorProfile', 'organizations:id,name')
                    ->filters($filters)
                    ->paginate($perPage);
        return $instructors;
    }

    public function findById(int $id)
    {
        return User::with('instructorProfile','organizations:id,name')
        ->findOrFail($id);
    }

    public function create(array $data)
    {
        // when an organization wants to add new instructor
       // the instructor might have an account on the platform (users table) created by another organization or by platform admin
       // we use firstorCreate to avoid duplication errors

       return DB::transaction(function() use($data) {

            //1. seperate basic information of instructor specific informtion
            $userData = Arr::only($data,['name','email','password','gender','date_of_birth','phone','address']);
            $instructorData = Arr::except($data,['name','email','password','gender','date_of_birth','phone','address']);

            //2. create user
            $user = User::firstOrCreate(['email' => $userData['email']],$userData);
            

            //3. create instructor profile

            // user_id = $user->id
            $instructor = $user->instructorProfile()->updateOrCreate(['user_id' => $user->id],$instructorData);
            //4. attach to organization
            $user->organizations()->attach($data['organization_id'],['role'=>UserRole::INSTRUCTOR->value]);
            //5. assign role
            $user->assignRole(UserRole::INSTRUCTOR->value);
            return $instructor;
       });  
    }

    public function update(User $user,array $data)
    {
        return DB::transaction(function () use ($data, $user) {
        
            $user->update(Arr::only($data,['name','email','password','gender','date_of_birth','phone','address']));           
            $user->instructorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                Arr::except($data,['name','email','password','gender','date_of_birth','phone','address'])
            );
            return $user->refresh();
        });
    }

    public function delete(User $user)
    {
        DB::transaction(function() use($user){
            $user->instructorProfile()->delete();
            $user->delete();
        });
        
    }
}
