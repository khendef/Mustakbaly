<?php

namespace Modules\UserManagementModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\UserManagementModule\App\Models\Builders\AuditorBuilder;
// use Modules\UserManagementModule\Database\Factories\AuditorFactory;

class Auditor extends Model
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

    public function newEloquentBuilder($query): AuditorBuilder
    {
        return new  AuditorBuilder($query);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
