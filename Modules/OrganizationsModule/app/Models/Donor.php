<?php
namespace Modules\OrganizationsModule\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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
         ->using(DonorProgram::class)
            ->withPivot('contribution_amount')
            ->withTimestamps();
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters)) {
            ksort($filters);
        }

        return $query
            ->when($filters['user_id'] ?? null, fn ($q, $userId) =>
                $q->where('user_id', $userId)
            )
            ->when($filters['name'] ?? null, fn ($q, $name) =>
                $q->whereJsonContains('name', $name)
            )
            ->when($filters['created_from'] ?? null, fn ($q, $date) =>
                $q->whereDate('created_at', '>=', $date)
            )
            ->when($filters['created_to'] ?? null, fn ($q, $date) =>
                $q->whereDate('created_at', '<=', $date)
            );
    }
}
