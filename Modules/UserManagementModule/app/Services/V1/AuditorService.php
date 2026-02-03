<?php

namespace Modules\UserManagementModule\Services\V1;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\UserManagementModule\DTOs\AuditorDTO;
use Modules\UserManagementModule\Enums\UserRole;
use Modules\UserManagementModule\Models\User;

class AuditorService
{
    private const CACHE_TTL = 3600;
    private const TAG_GLOBAL = 'auditors';
    private const TAG_PREFIX_AUDITOR = 'auditor_';
    public function list($filters, int $perPage=15)
    {
        $orgId = config('app.current_organization_id');
        $orgkey = $orgId ? "_org_{$orgId}" : '';
        
        ksort($filters);
        $filtersKey = md5(json_encode($filters));
        $CacheKey = "auditors_list_{$filtersKey}_limit_{$perPage}_{$orgkey}";     

        return Cache::tags([self::TAG_GLOBAL])->remember($CacheKey,self::CACHE_TTL,function() use($filters, $perPage){
            $auditors = User::whereHas('auditorProfile')
                        ->with('auditorProfile', 'organizations:id,name')
                        //->filters($filters)
                        ->paginate($perPage);
            return $auditors;
        });
    }

    public function findById(int $id)
    {
        $orgId = config('app.current_organization_id'); 
        $orgkey = $orgId ? "_org_{$orgId}" : '';
        $cacheKey = "auditor_details_{$id}_{$orgkey}";
        
        return Cache::tags([self::TAG_GLOBAL, self::TAG_PREFIX_AUDITOR . $id])->remember($cacheKey, self::CACHE_TTL, function() use($id) {
            return User::with('media','auditorProfile','organizations:id,name')
            ->findOrFail($id);
        });
    }

    public function create(AuditorDTO $auditorDTO)
    {
        return DB::transaction(function() use($auditorDTO) {

            //1. seperate basic information of auditor specific informtion
            $userData = $auditorDTO->userData();
            $auditorData = $auditorDTO->auditorData();

            //2. create user
            $user = User::firstOrCreate(['email' => $userData['email']],$userData);


            //3. create auditor profile
            if (isset($auditorDTO->avatar)) {
                $user->addMedia($auditorDTO->avatar)->toMediaCollection('avatar');
            }

            $user->auditorProfile()->updateOrCreate(['user_id' => $user->id],$auditorData);
            if (isset($auditorDTO->cv)) {
            $user->addMedia($auditorDTO->cv)->toMediaCollection('cv');
        }
            //4. attach to organization
            $user->organizations()->syncWithoutDetaching($auditorDTO->organizationId,['role'=>UserRole::AUDITOR->value]);
            //5. assign role
            $user->assignRole(UserRole::AUDITOR->value);
            return $user->load('auditorProfile');
       });
    }

    public function update(User $user,AuditorDTO $auditorDTO)
    {
        return DB::transaction(function () use ($auditorDTO, $user) {
        
            $user->update($auditorDTO->userData());  
                  if (isset($auditorDTO->avatar)) {
            $user->addMedia($auditorDTO->avatar)->toMediaCollection('avatar');
        }
            $user->auditorProfile()->update($auditorDTO->auditorData());

            if (isset($auditorDTO->cv)) {
                $user->addMedia($auditorDTO->cv)->toMediaCollection('cv');
            }
            return $user->load('auditorProfile')->refresh();
        });


    }

    public function delete(User $user)
    {
        DB::transaction(function() use($user){
            $user->auditorProfile()->delete();
            $user->delete();
        });

    }
}


