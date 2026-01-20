<?php
namespace Modules\OrganizationsModule\Models;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class Donor extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
       'user_id',
       'description',
       'name',
    ];

    // Relationship with User

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Programs
     public function programs()
    {
        return $this->belongsToMany(Program::class, 'donor_program')
            ->withPivot('contribution_amount')
            ->withTimestamps();
    }
}
