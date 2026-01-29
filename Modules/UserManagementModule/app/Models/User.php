<?php

namespace Modules\UserManagementModule\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\OrganizationsModule\Models\Organization;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Modules\UserManagementModule\App\Models\Builders\UserBuilder;
use Modules\UserManagementModule\Models\Scopes\OrganizationScope;
// use Modules\UserManagementModule\Database\Factories\UserFactory;

class User extends Model implements JWTSubject , HasMedia
{
/** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable,  HasRoles, SoftDeletes , InteractsWithMedia;

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

     protected static function booted()
    {
        static::addGlobalScope(new OrganizationScope);
    }

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

    public function studentProfile()
    {
        return $this->hasOne(Student::class);
    }
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

public function registerMediaCollections(): void
{
    $this->addMediaCollection('avatar')
         ->singleFile() // المستخدم يملك صورة شخصية واحدة فقط
         ->useFallbackUrl(asset('images/default-avatar.png'));
}

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
         ->width(100)
         ->height(100)
         ->nonQueued();

    $this->addMediaConversion('preview')
         ->width(300)
         ->height(300)
         ->queued();
}

}
