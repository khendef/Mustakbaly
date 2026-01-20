<?php
namespace Modules\CertificationModule\Models;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
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

}
