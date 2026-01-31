<?php

namespace Modules\UserManagementModule\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Modules\OrganizationsModule\Models\Organization;
use Modules\UserManagementModule\Models\Builders\UserBuilder;
use Modules\UserManagementModule\Models\Scopes\OrganizationScope;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
// use Modules\UserManagementModule\Database\Factories\UserFactory;

class User extends Authenticatable implements JWTSubject
{
/** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable,  HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'date_of_birth',
        'gender'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard_name = 'api';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // protected static function booted()
    // {
    //     static::addGlobalScope(new OrganizationScope);
    // }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
 

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }

   // public function studentProfile()
    ////{
       // return $this->hasOne(Student::class);
   // }
    public function instructorProfile()
    {
        return $this->hasOne(instructor::class);
    }
    public function auditorProfile()
    {
        return $this->hasOne(auditor::class);
    }
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
                ->withPivot('role')
                ->withTimestamps();
    }
 
}
