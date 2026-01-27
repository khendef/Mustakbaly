<?php
namespace Modules\CertificationModule\Models;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\OrganizationsModule\Models\Organization;

class Certificate extends Model
{
    protected $table = 'certificates';

    protected $fillable = [
        'enrollment_id',
        'organization_id',
        'certificate_number',
        'completion_date',
        'issue_date',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'issue_date' => 'date',
    ];

    // Relationship with Enrollment
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Relationship with Organization
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Accessors for formatted dates
        public function getFormattedIssueDateAttribute(): string
    {
        return $this->issue_date?->format('Y-m-d');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['organization_id'] ?? null,
                fn ($q, $value) => $q->where('organization_id', $value)
            )
            ->when($filters['issue_date_from'] ?? null,
                fn ($q, $value) => $q->whereDate('issue_date', '>=', $value)
            )
            ->when($filters['issue_date_to'] ?? null,
                fn ($q, $value) => $q->whereDate('issue_date', '<=', $value)
            )
            ->when($filters['certificate_number'] ?? null,
                fn ($q, $value) => $q->where('certificate_number', 'like', "%{$value}%")
            );
    }
}
