<?php
/**
 * User model for the UserManagementModule.
 *
 * Logic:
 * - Extends Laravel's `Authenticatable` to act as an auth-capable user model.
 * - Implements `JWTSubject` for JWT token integration.
 *
 * @package Modules\UserManagementModule\Models
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $password
 * @property string|null $phone
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property string|null $gender
 */

namespace Modules\UserManagementModule\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
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
  use HasFactory, Notifiable,  HasRoles, SoftDeletes, CascadeSoftDeletes;

     /**
     * The attributes that are mass assignable.
     *
     * We define this list to protect against Mass Assignment Vulnerabilities.
     * Only these fields can be updated via `User::create($request->all())`.
     *
     * @var list<string>
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

    protected $cascadeDeletes = ['studentProfile','instructorProfile','auditorProfile'];

    protected $guard_name = 'api';

    /**
     * Return the attribute casting map for the model.
     *
     * Logic:
     * - Declares how specific attributes should be cast when accessed or set.
     * - 'email_verified_at' => 'datetime' ensures datetime instance.
     * - 'password' => 'hashed' uses Laravel's native hashed casting when setting passwords.
     * - 'date_of_birth' => 'date' casts to a date instance.
     *
     * @return array<string, string> Array of attribute => cast type.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date'
        ];
    }
    /**
     * The "booted" method of the model.
     *
     * Logic:
     * - Used to define model event hooks and global scopes.
     * - Here, we would add the OrganizationScope to limit queries by organization.
     */

    // protected static function booted()
    // {
    //     static::addGlobalScope(new OrganizationScope);
    // }


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * Logic:
     * - Returns the model primary key via `$this->getKey()`.

     * - Used by the JWT library as the unique identifier (sub) for the token.
     *
     * @return mixed The primary key value of the user model.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
 

    public function getJWTCustomClaims()
    {
        return [];
    }


    /**
     * Create a new Eloquent query builder instance for the model.
     *
     * Logic:
     * - Returns an instance of `UserBuilder` to provide custom query scope/methods
     *   specific to the `User` model.
     * - Allows using fluent, reusable query helpers typed to `UserBuilder`.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Modules\UserManagementModule\Models\Builders\UserBuilder
     */
    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }



    /**
     * Define a one-to-one relationship to the student profile.
     *
     * Logic:
     * - `hasOne(Student::class)` indicates a user may have a single Student profile.
     * - This relation is included in `cascadeDeletes` so deleting the user will
     *   also remove the related student profile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function studentProfile()
    {
        return $this->hasOne(Student::class);
    }



    /**
     * Define a one-to-one relationship to the instructor profile.
     *
     * Logic:
     * - `hasOne(instructor::class)` indicates a user may have a single Instructor profile.
     * - Included in `cascadeDeletes` so related instructor profile is removed on user delete.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function instructorProfile()
    {
        return $this->hasOne(instructor::class);
    }




    /**
     * Define a one-to-one relationship to the auditor profile.
     *
     * Logic:
     * - `hasOne(auditor::class)` indicates a user may have a single Auditor profile.
     * - This relation is included in `cascadeDeletes` so deleting the user will
     *   also remove the related auditor profile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function auditorProfile()
    {
        return $this->hasOne(auditor::class);
    }




    
    /**
     * Define a many-to-many relationship between users and organizations.
     *
     * Logic:
     * - `belongsToMany(Organization::class, 'organization_user')` uses the pivot table
     *   `organization_user` to link users and organizations.
     * - `withPivot('role')` exposes the pivot column `role` on the relation (user role within organization).
     * - `withTimestamps()` automatically maintains pivot `created_at` and `updated_at`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
                ->withPivot('role')
                ->withTimestamps();
    }
 
}
