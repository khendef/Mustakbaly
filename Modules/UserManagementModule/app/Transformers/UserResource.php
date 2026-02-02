<?php

namespace Modules\UserManagementModule\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $hasOrgs = $this->relationLoaded('organizations') && $this->organizations->isNotEmpty();

        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'gender' => $this->gender,
            'avatar' => [
                'original' => $this->getFirstMediaUrl('avatar'),
                'thumb'    => $this->getFirstMediaUrl('avatar', 'thumb'),
                'preview'  => $this->getFirstMediaUrl('avatar', 'preview'),
            ],

            'roles' => $this->when(!$hasOrgs, fn()=>$this->whenLoaded
                ('roles', function() {
                    return $this->roles->map(function($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'permissions' => $role->permissions->pluck('name'),
                        ];
                    });
                })),

            'organizations' => $this->whenLoaded('organizations',function(){
                return $this->organizations->map(function ($org) {
                    $roleName = $org->pivot->role;
                    $role = Role::where('name', $roleName)->first();
                    return [
                        'id'   => $org->id,
                        'name' => $org->name,
                        'role' => $role,
                        'permissions' => $role->permissions()->pluck('name'),
                        'profile' => match($role) {
                            'instructor' => $this->whenLoaded('instructorProfile'),
                            'student' => $this->whenLoaded('studentProfile'),
                            'auditor' => $this->whenLoaded('auditorProfile'),
                            default => null,
                        },
                    ];
                });
            }),
        ];

    }
}
