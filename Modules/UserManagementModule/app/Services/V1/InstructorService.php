<?php

namespace Modules\UserManagementModule\Services\V1;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\UserManagementModule\DTOs\InstructorDTO;
use Modules\UserManagementModule\Enums\UserRole;
use Modules\UserManagementModule\Models\User;

class InstructorService
{
    private const CACHE_TTL = 3600;
    private const TAG_GLOBAL = 'instructors';
    private const TAG_PREFIX_INSTRUCTOR = 'instructor_';
    public function list($filters, int $perPage=15)
    {
        $orgId = config('app.current_organization_id');
        $orgkey = $orgId ? "_org_{$orgId}" : '';
        
        ksort($filters);
        $filtersKey = md5(json_encode($filters));
        $CacheKey = "instructors_list_{$filtersKey}_limit_{$perPage}_{$orgkey}";     

        return Cache::tags([self::TAG_GLOBAL])->remember($CacheKey,self::CACHE_TTL,function() use($filters, $perPage){
            $instructors = User::whereHas('instructorProfile')
                        ->with('media','instructorProfile', 'organizations:id,name')
                        //->filters($filters)
                        ->paginate($perPage);
            return $instructors;
        });
    }

    public function findById(int $id)
    {
        $orgId = config('app.current_organization_id');
        $orgkey = $orgId ? "_org_{$orgId}" : '';
        $cacheKey = "instructor_details_{$id}_{$orgkey}";
        return Cache::tags([self::TAG_GLOBAL, self::TAG_PREFIX_INSTRUCTOR . $id])->remember($cacheKey, self::CACHE_TTL, function() use($id) {
            return User::with('media','instructorProfile','organizations:id,name')
            ->findOrFail($id);
        });
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

            $user->instructorProfile()->updateOrCreate(['user_id' => $user->id],$instructorData);

            if (isset($instructorDTO->cv)) {
              $user->addMedia($instructorDTO->cv)->toMediaCollection('cv');
            }
            //4. attach to organization
            $user->organizations()->syncWithoutDetaching($instructorDTO->organizationId,['role'=>UserRole::INSTRUCTOR->value]);
            //5. assign role
            $user->assignRole(UserRole::INSTRUCTOR->value);
            return $user->load('instructorProfile');
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
            return $user->load('instructorProfile')->refresh();
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
