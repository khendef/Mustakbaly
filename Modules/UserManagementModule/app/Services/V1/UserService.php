<?php
/**
 * Create a new user inside a DB transaction and optionally assign a role.
 *
 * Logic:
 * - Runs creation in a DB transaction: either all changes persist or none do.
 * - Calls `User::create($data)` to create the model (relies on model fillable).
 * - If a `role` key is provided in `$data`, assigns that role to the user.
 *
 * @param array $data Attributes for creating the user. May include 'role'.
 * @return \Modules\UserManagementModule\Models\User The created user model.
 *
 * @throws \Throwable Any exception will bubble up and cause the transaction to rollback.
 */

namespace Modules\UserManagementModule\Services\V1;

use Illuminate\Support\Facades\DB;
use Modules\UserManagementModule\Enums\UserRole;
use Modules\UserManagementModule\Models\User;
use Modules\UserManagementModule\Transformers\UserResource;

class UserService
{
    public function list($filters, int $perPage=15)
    {
        $users = User::with('media','organizations:id,name')
                    ->filter($filters)
                    ->paginate($perPage);
        return $users;
    }

    /**
     * Retrieve a single user by id and return as a resource with related data.
     *
     * Logic:
     * - Eager-loads `roles` (only `id` and `name`) and those roles' `permissions`,
     *   plus `organizations` (`id` and `name`) to minimize queries.
     * - Uses `findOrFail` so a missing user will raise a ModelNotFoundException.
     * - Calls `loadProfile` to eager-load the appropriate profile relation
     *   (student/instructor/auditor) based on the user's roles.
     * - Wraps the resulting model in `UserResource` for consistent API output.
     *
     * @param int $id User primary key.
     * @return \Modules\UserManagementModule\Transformers\UserResource Transformed user resource.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no user found.
     */
    public function findById(int $id)
    {
        $user = User::with(['roles'=>function($q){
                        $q->select('id','name')
                        -> with('permissions:id,name');
                } ,'organizations:id,name'])->findOrFail($id);

        $user = $this->loadProfile($user,$user->getRoleNames()->toArray());
        return new UserResource($user);
    }

    /**
     * Summary of create
     * @param array $data
     */
    public function create(array $data)
    {
        return DB::transaction(function() use($data) {

            $user = User::Create($data);
            if(isset($data['role'])){
                $user->assignRole($data['role']);
            }
             if (isset($data['avatar'])) {
            $user->addMedia($data['avatar'])->toMediaCollection('avatar');
        }
            return $user;
        });
    }

    /**
     * Update an existing user model and return the refreshed instance.
     *
     * Logic:
     * - Calls `$user->update($data)` to apply mass-assignable changes.
     * - Returns `$user->refresh()` to ensure latest relations/attributes are loaded.
     *
     * @param \Modules\UserManagementModule\Models\User $user The user instance to update.
     * @param array $data Attributes to update on the user.
     * @return \Modules\UserManagementModule\Models\User The updated (refreshed) user model.
     */
    public function update(User $user,array $data)
    {
        $user->update($data);
        if (isset($data['avatar'])) {
            $user->addMedia($data['avatar'])->toMediaCollection('avatar');
        }
        return $user->refresh();
    }

    /**
     * Eager-load profile relation(s) for a user based on assigned roles.
     *
     * Logic:
     * - Maps known role values (via `UserRole` enum) to profile relationship names:
     *   instructor => 'instructorProfile', auditor => 'auditorProfile', student => 'studentProfile'.
     * - Iterates the map and calls `$user->load($relation)` if the role exists in `$roles`.
     * - Ensures only relevant profile relations are loaded (avoids unnecessary queries).
     *
     * @param \Modules\UserManagementModule\Models\User $user The user model to load relations on.
     * @param string[] $roles Array of role names assigned to the user.
     * @return \Modules\UserManagementModule\Models\User The same user instance (with requested relations loaded).
     */
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
