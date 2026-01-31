<?php

namespace Modules\UserManagementModule\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\UserManagementModule\App\Models\Builders\InstructorBuilder;
=======
use Modules\UserManagementModule\Models\Builders\InstructorBuilder;
>>>>>>> 8f82310be1ed3956233161a9a739ff5b62ca6e3c
// use Modules\UserManagementModule\Database\Factories\InstructorFactory;

class Instructor extends Model implements HasMedia
{
    use HasFactory, SoftDeletes , InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'specialization',
        'bio',
        'years_of_experience'
    ];

    public function newEloquentBuilder($query): InstructorBuilder
    {
        return new InstructorBuilder($query);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cv')
             ->acceptsMimeTypes(['application/pdf'])
             ->singleFile();
    }
}
