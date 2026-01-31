<?php

namespace Modules\UserManagementModule\Services\V1;

use Modules\UserManagementModule\Enums\UserRole;
use Modules\UserManagementModule\Models\User;
use Modules\UserManagementModule\Transformers\UserResource;

class UserService
{
    public function list($filters, int $perPage=15)
    {
        $users = User::with('media','organizations:id,name')
                    ->filters($filters)
                    ->paginate($perPage);
        return $users;
    }

    public function findById(int $id)
    {
        $user = User::with(['media','roles:id,name'=>fn($q)=>$q->with('permissions:id,name') ,'organizations:id,name'])
            ->findOrFail($id);

        $user = $this->loadProfile($user,$user->getRoleNames()->toArray());
        return new UserResource($user);
    }

    public function create(array $data)
    {
        $user = User::create($data);
<<<<<<< HEAD
        $user->assignRole($data['role']);
        if (isset($data['avatar'])) {
            $user->addMedia($data['avatar'])->toMediaCollection('avatar');
=======
        if(isset($data['role'])){
            $user->assignRole($data['role']);
>>>>>>> 8f82310be1ed3956233161a9a739ff5b62ca6e3c
        }
        return $user;
    }

    public function update(User $user,array $data)
    {
        $user->update($data);

        if(isset($data['role'])){
            $user->syncRoles($data['role']);
        }
        if (isset($data['avatar'])) {
            $user->addMedia($data['avatar'])->toMediaCollection('avatar');
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
