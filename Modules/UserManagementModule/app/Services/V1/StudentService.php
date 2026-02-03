<?php

namespace Modules\UserManagementModule\Services\V1;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\UserManagementModule\DTOs\ProfileDTO;
use Modules\UserManagementModule\DTOs\StudentDTO;
use Modules\UserManagementModule\Enums\UserRole;
use Modules\UserManagementModule\Http\Requests\Api\V1\Student\StudentStoreRequest;
use Modules\UserManagementModule\Models\User;

class StudentService
{
    private const CACHE_TTL = 3600;
    private const TAG_GLOBAL = 'students';
    private const TAG_PREFIX_STUDENT = 'student_';
    public function list($filters, int $perPage=15)
    {
        $orgId = config('app.current_organization_id');
        $orgkey = $orgId ? "_org_{$orgId}" : '';
        
        ksort($filters);
        $filtersKey = md5(json_encode($filters));
        $CacheKey = "students_list_{$filtersKey}_limit_{$perPage}_{$orgkey}";     

        return Cache::tags([self::TAG_GLOBAL])->remember($CacheKey,self::CACHE_TTL,function() use($filters, $perPage){
            $students = User::whereHas('studentProfile')
                        ->with('media','studentProfile', 'courses:id,name')
                        ->filters($filters)
                        ->paginate($perPage);
            return $students;
        });
    }

    public function findById(int $id)
    {
        $orgId = config('app.current_organization_id');
        $orgkey = $orgId ? "_org_{$orgId}" : '';
        $cacheKey = "student_details_{$id}_{$orgkey}";
        return Cache::tags([self::TAG_GLOBAL, self::TAG_PREFIX_STUDENT . $id])->remember($cacheKey, self::CACHE_TTL, function() use($id) {
            return User::with('media','studentProfile','courses:id,name')
            ->findOrFail($id);
        });
    }

    public function create(StudentDTO $studentDTO)
    {
        return DB::transaction(function() use($studentDTO) {

            //1. seperate basic information of student specific informtion
            $userData = $studentDTO->userData();
            $studentData = $studentDTO->studentData();

            //2. create user
            $user = User::firstOrCreate(['email' => $userData['email']],$userData);


            //3. create student profile
            if (isset($studentDTO->avatar)) {
            $user->addMedia($studentDTO->avatar)->toMediaCollection('avatar');
            }

            // user_id = $user->id
            $student = $user->studentProfile()->updateOrCreate(['user_id' => $user->id],$studentData);
            //4. attach to organization
            $user->organizations()->syncWithoutDetaching($studentDTO->organizationId,['role'=>UserRole::STUDENT->value]);
            //5. assign role
            $user->assignRole(UserRole::STUDENT->value);
            return $student;
       });
    }

    public function update(User $user, StudentDTO $studentDTO)
    {
        return DB::transaction(function () use ($studentDTO, $user) {
        
            $user->update($studentDTO->userData());   
             if (isset($studentDTO->avatar)) {
            $user->addMedia($studentDTO->avatar)->toMediaCollection('avatar');
            }
            $user->studentProfile()->update($studentDTO->studentData());
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
    

 
}
