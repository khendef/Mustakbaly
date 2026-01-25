<?php

namespace Modules\UserManagementModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\UserManagementModule\App\Models\Builders\StudentBuilder;
use Modules\UserManagementModule\Enums\EducationalLevel;
// use Modules\UserManagementModule\Database\Factories\StudentFactory;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'educational_level',
        'country'

    ];

    protected function casts(): array
    {
        return [
            'status'=>EducationalLevel::class
        ];
    }

    public function newEloquentBuilder($query): StudentBuilder
    {
        return new StudentBuilder($query);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
