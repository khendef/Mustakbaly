<?php

namespace Modules\UserManagementModule\Services\V1;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\UserManagementModule\DTOs\AuditorDTO;
use Modules\UserManagementModule\Enums\UserRole;
use Modules\UserManagementModule\Models\User;

class AuditorService
{
    public function list($filters, int $perPage=15)
    {
        $auditors = User::whereHas('auditorProfile')
                    ->with('media','auditorProfile', 'organizations:id,name')
                    ->filters($filters)
                    ->paginate($perPage);
        return $auditors;
    }

    public function findById(int $id)
    {
        return User::with('media','auditorProfile','organizations:id,name')
        ->findOrFail($id);
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

            // user_id = $user->id
            $auditor = $user->auditorProfile()->updateOrCreate(['user_id' => $user->id],$auditorData);
            if (isset($auditorDTO->cv)) {
            $auditor->addMedia($auditorDTO->cv)->toMediaCollection('cv');
        }
            //4. attach to organization
            $user->organizations()->attach($auditorDTO->organizationId,['role'=>UserRole::AUDITOR->value]);
            //5. assign role
            $user->assignRole(UserRole::AUDITOR->value);
            return $auditor;
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
            return $user->refresh();
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


