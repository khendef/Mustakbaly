<?php

/**
 * AuditorService
 *
 * Service responsible for managing auditor users: listing, retrieving,
 * creating, updating and deleting auditors. Includes caching helpers
 * for list and detail retrieval.
 *
 * @package Modules\UserManagementModule\Services\V1
 */
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
    /**
     * Get a paginated list of auditors.
     *
     * Results are cached using a key derived from the supplied filters,
     * the page size and the current organization context.
     *
     * @param array $filters  Associative array of filters to apply.
     * @param int $perPage    Number of items per page (default 15).
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
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
    /**
     * Find an auditor by id, including related media and profile.
     *
     * The result is cached and tagged for invalidation when the auditor
     * or global auditors data changes.
     *
     * @param int $id
     * @return \Modules\UserManagementModule\Models\User
     */
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
    /**
     * Create a new auditor user along with their auditor profile,
     * uploaded media (avatar, CV), and organization membership.
     *
     * This operation is executed inside a database transaction.
     *
     * @param \Modules\UserManagementModule\DTOs\AuditorDTO $auditorDTO
     * @return \Modules\UserManagementModule\Models\User
     */
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
    /**
     * Update an existing auditor user and their profile.
     *
     * Updates user basic data, profile data and optional media. Runs
     * inside a database transaction and returns the refreshed user.
     *
     * @param \Modules\UserManagementModule\Models\User $user
     * @param \Modules\UserManagementModule\DTOs\AuditorDTO $auditorDTO
     * @return \Modules\UserManagementModule\Models\User
     */
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
    /**
     * Delete an auditor and their auditor profile inside a transaction.
     *
     * @param \Modules\UserManagementModule\Models\User $user
     * @return void
     */
    public function delete(User $user)
    {
        DB::transaction(function() use($user){
            $user->auditorProfile()->delete();
            $user->delete();
        });

    }
}


