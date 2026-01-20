<?php
namespace Modules\OrganizationsModule\Models;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    protected $table = 'programs';

    protected $fillable = [
        'organization_id',
        'title',
        'description',
        'objectives',
        'status',
        'required_budget',
        'total_funded_amount'
    ];

    // Relationship with Organization
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    // Relationship with Donors
    public function donors()
    {
        return $this->belongsToMany(Donor::class, 'donor_program')
            ->withPivot('contribution_amount');
    }

    /* ================= Accessors ================= */

    public function getFundingPercentageAttribute(): float
    {
        return $this->required_budget > 0
            ? round(($this->total_funded_amount / $this->required_budget) * 100, 2)
            : 0;
    }

    public function getIsFullyFundedAttribute(): bool
    {
        return $this->total_funded_amount >= $this->required_budget;
    }

        /* ================= Mutators ================= */

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = trim(ucfirst($value));
    }

    public function setRequiredBudgetAttribute($value)
    {
        $this->attributes['required_budget'] = max(0, (float) $value);
    }

    protected $casts = [
        'required_budget' => 'float',
        'total_funded_amount' => 'float',
    ];


}
