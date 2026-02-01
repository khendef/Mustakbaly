<?php
namespace Modules\OrganizationsModule\Models;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Organization extends Model implements HasMedia

{
    use HasTranslations , SoftDeletes , InteractsWithMedia;
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

public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
             ->singleFile()
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/svg+xml']);
    }

    public function registerMediaConversions(? Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(150)
             ->height(150)
             ->sharpen(10)
             ->nonQueued();

        $this->addMediaConversion('optimized')
             ->width(800)
             ->quality(80)
             ->withResponsiveImages()
             ->queued('medialibrary');
    }

}
