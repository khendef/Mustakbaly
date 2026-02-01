<?php

namespace Modules\UserManagementModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\UserManagementModule\Models\Builders\InstructorBuilder;
// use Modules\UserManagementModule\Database\Factories\InstructorFactory;

class Instructor extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'specialization',
        'bio',
        'years_of_experience'
    ];

    protected function casts(): array
    {
        return [
            'years_of_experience' => 'integer'
        ];
    }

    public function newEloquentBuilder($query): InstructorBuilder
    {
        return new InstructorBuilder($query);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
