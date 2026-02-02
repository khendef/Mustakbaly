<?php
namespace Modules\OrganizationsModule\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Modules\UserManagementModule\Models\User;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model

{
    use HasTranslations , SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'slug',
        'description',
        'logo',
    ];

    /**
     * * Translatable attributes.
     *  */
    public array $translatable = ['name', 'description'];

     //relationship with programs
     public function programs() {
        return $this->hasMany(Program::class);
     }
    // Cast description to array
     protected $casts = [
        'description' => 'array',
        'name' => 'array',
    ];

    // Add an accessor for a short description
    public function getShortDescriptionAttribute(): string
    {
    return str($this->getTranslation('description', app()->getLocale()) ?? '')
        ->limit(100);
     }

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_user')
                ->withPivot('role')
                ->withTimestamps();
    }
// Automatically generate slug from name if not provided
protected static function booted()
{
    static::creating(function ($organization) {
        if (empty($organization->slug)) {
            $organization->slug = Str::slug(
                $organization->getTranslation('name', app()->getLocale())
            );
        }
    });
}
}
