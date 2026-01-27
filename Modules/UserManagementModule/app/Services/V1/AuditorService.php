<?php

namespace Modules\UserManagementModule\Services\V1;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\UserManagementModule\Enums\UserRole;
use Modules\UserManagementModule\Models\User;

class AuditorService
{
    public function list($filters, int $perPage=15)
    {
        $auditors = User::whereHas('auditorProfile')
                    ->with('auditorProfile', 'organizations:id,name')
                    ->filters($filters)
                    ->paginate($perPage);
        return $auditors;
    }

    public function findById(int $id)
    {
        return User::with('auditorProfile','organizations:id,name')
        ->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function() use($data) {

            //1. seperate basic information of auditor specific informtion
            $userData = Arr::only($data,['name','email','password','gender','date_of_birth','phone','address']);
            $auditorData = Arr::except($data,['name','email','password','gender','date_of_birth','phone','address']);

            //2. create user
            $user = User::firstOrCreate(['email' => $userData['email']],$userData);
            

            //3. create auditor profile

            // user_id = $user->id
            $auditor = $user->auditorProfile()->updateOrCreate(['user_id' => $user->id],$auditorData);
            //4. attach to organization
            $user->organizations()->attach($data['organization_id'],['role'=>UserRole::AUDITOR->value]);
            //5. assign role
            $user->assignRole(UserRole::AUDITOR->value);
            return $auditor;
       }); 
    }

    public function update(User $user,array $data)
    {
        return DB::transaction(function () use ($data, $user) {
        
            $user->update(Arr::only($data,['name','email','password','gender','date_of_birth','phone','address']));           
            $user->auditorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                Arr::except($data,['name','email','password','gender','date_of_birth','phone','address'])
            );
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


