<?php

namespace Modules\UserManagementModule\Services\V1;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\UserManagementModule\DTOs\InstructorDTO;
use Modules\UserManagementModule\Enums\UserRole;
use Modules\UserManagementModule\Models\User;

class InstructorService
{
    public function list($filters, int $perPage=15)
    {
        $instructors = User::whereHas('instructorProfile')
                    ->with('media','instructorProfile', 'organizations:id,name')
                    ->filters($filters)
                    ->paginate($perPage);
        return $instructors;
    }

    public function findById(int $id)
    {
        return User::with('media','instructorProfile','organizations:id,name')
        ->findOrFail($id);
    }

    public function create( InstructorDTO $instructorDTO)
    {
        // when an organization wants to add new instructor
       // the instructor might have an account on the platform (users table) created by another organization or by platform admin
       // we use firstorCreate to avoid duplication errors

       return DB::transaction(function() use($instructorDTO) {

            //1. seperate basic information of instructor specific informtion
            $userData = $instructorDTO->userData();
            $instructorData =$instructorDTO->instructorData();

            //2. create user
            $user = User::firstOrCreate(['email' => $userData['email']],$userData);

            //3. create instructor profile
            if (isset($instructorDTO->avatar)) {
            $user->addMedia($instructorDTO->avatar)->toMediaCollection('avatar');
        }

            // user_id = $user->id
            $instructor = $user->instructorProfile()->updateOrCreate(['user_id' => $user->id],$instructorData);
            if (isset($instructorDTO->cv)) {
              $instructor->addMedia($instructorDTO->cv)->toMediaCollection('cv');
        }
            //4. attach to organization
            $user->organizations()->syncWithoutDetaching($instructorDTO->organizationId,['role'=>UserRole::INSTRUCTOR->value]);
            //5. assign role
            $user->assignRole(UserRole::INSTRUCTOR->value);
            return $instructor;
       });
    }

    public function update(User $user,InstructorDTO $instructorDTO)
    {
        return DB::transaction(function () use ($instructorDTO, $user) {
            $user->update($instructorDTO->userData());     
                 if (isset($instructorDTO->avatar)) {
            $user->addMedia($instructorDTO->avatar)->toMediaCollection('avatar');
        }
            $user->instructorProfile()->update($instructorDTO->instructorData());
               if (isset($instructorDTO->cv)) {
            $user->addMedia($instructorDTO->cv)->toMediaCollection('cv');
        }
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
