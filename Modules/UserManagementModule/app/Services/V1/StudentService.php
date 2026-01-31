<?php

namespace Modules\UserManagementModule\Services\V1;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\UserManagementModule\Enums\UserRole;
use Modules\UserManagementModule\Http\Requests\Api\V1\Student\StudentStoreRequest;
use Modules\UserManagementModule\Models\User;

class StudentService
{
    public function list($filters, int $perPage=15)
    {
        $students = User::whereHas('studentProfile')
                    ->with('media','studentProfile', 'courses:id,name')
                    ->filters($filters)
                    ->paginate($perPage);
        return $students;
    }

    public function findById(int $id)
    {
        return User::with('media','studentProfile','courses:id,name')
        ->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function() use($data) {

            //1. seperate basic information of student specific informtion
            $userData = Arr::only($data,['name','email','password','gender','date_of_birth','phone','address']);
            $studentData = Arr::except($data,['name','email','password','gender','date_of_birth','phone','address']);

            //2. create user
            $user = User::firstOrCreate(['email' => $userData['email']],$userData);


            //3. create student profile
            if (isset($data['avatar'])) {
            $user->addMedia($data['avatar'])->toMediaCollection('avatar');
            }

            // user_id = $user->id
            $student = $user->studentProfile()->updateOrCreate(['user_id' => $user->id],$studentData);
            //4. attach to organization
            $user->organizations()->attach($data['organization_id'],['role'=>UserRole::STUDENT->value]);
            //5. assign role
            $user->assignRole(UserRole::STUDENT->value);
            return $student;
       });
    }

    public function update(User $user,array $data)
    {
        return DB::transaction(function () use ($data, $user) {

            $user->update(Arr::only($data,['name','email','password','gender','date_of_birth','phone','address']));
            $user->studentProfile()->updateOrCreate(
                ['user_id' => $user->id],
                Arr::except($data,['name','email','password','gender','date_of_birth','phone','address'])
            );
            if (isset($data['avatar'])) {
            $user->addMedia($data['avatar'])->toMediaCollection('avatar');
            }

            return $user->refresh();

        });
    }

    public function delete(User $user)
    {
        DB::transaction(function() use($user){
            $user->studentProfile()->delete();
            $user->delete();
        });

    }

    //'/complete-profile' studentController@fillProfileInfo'

    public function fillProfileInfo(array $data)
    {
        if(!auth()->check()){
            return [
                'message' => 'please sign in'
            ];
        }

        $user = auth()->user();
        $user->studentProfile()->updateOrCreate(['user_id' => $user->id],$data);
        $user->assignRole('student');
        return $user;
    }
    
      /**
     * enrollment process
     * transaction begins:
     * 1. check profile
     * 2. if not redirect to complete profile
     * 3. assign role student
     * 4. attach to organization
     * 5. enroll the course
     */

    public function registerStudent($orgId)
    {
        $user = auth()->user();
        //$orgId = $course->program->organization_id;
        if(!$user->studentProfile()->exists()){
            return [
                'message' => 'student information incomplete'
                // redirect to create new profile
            ];
        }
        $user->organizations()->syncWithoutDetaching([$orgId => ['role' => 'student']]);   

    }
 
}
