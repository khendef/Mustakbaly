<?php

namespace Modules\UserManagementModule\Services\V1;

use Modules\UserManagementModule\Enums\UserRole;
use Modules\UserManagementModule\Models\User;
use Modules\UserManagementModule\Transformers\UserResource;

class UserService
{
    public function list($filters, int $perPage=15)
    {
        $users = User::with('organizations:id,name')
                    ->filters($filters)
                    ->paginate($perPage);
        return $users;
    }

    public function findById(int $id)
    {
        $user = User::with(['roles:id,name'=>fn($q)=>$q->with('permissions:id,name') ,'organizations:id,name'])
            ->findOrFail($id);

        $user = $this->loadProfile($user,$user->getRoleNames()->toArray());
        return new UserResource($user);
    }

    public function create(array $data)
    {
        $user = User::create($data);
        $user->assignRole($data['role']);
        return $user;
    }

    public function update(User $user,array $data)
    {
        $user->update($data);
        if(isset($data['role'])){
            $user->syncRoles($data['role']);
        }

        return $user->refresh();
    }

    private function loadProfile(User $user,array $roles)
    {
        $profileMap = [
            UserRole::INSTRUCTOR->value => 'instructorProfile',
            UserRole::AUDITOR->value => 'auditorProfile',
            UserRole::STUDENT->value => 'studentProfile',
        ];

        foreach($profileMap as $role=>$profile){
            if(in_array($role,$roles)){
                $user->load($profile);
            }
        }
        return $user;
    }


}
