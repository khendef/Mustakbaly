<?php
namespace Modules\OrganizationsModule\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\UserManagementModule\Models\Scopes\OrganizationScope;

class Program extends Model
{
    use SoftDeletes;

    protected $table = 'programs';

    protected $fillable = [
        'title',
        'description',
        'objectives',
        'status',
        'required_budget',
        'total_funded_amount'
    ];
    
        /**
     * The "booted" method of the model.
     *
     * Logic:
     * - Used to define model event hooks and global scopes.
     * - Here, we would add the OrganizationScope to limit queries by organization.
     */

    protected static function booted()
    {
        static::addGlobalScope(new OrganizationScope());
    }
    
    // Relationship with Organization
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    // Relationship with Donors
    public function donors()
    {
        return $this->belongsToMany(Donor::class, 'donor_program')
           ->using(DonorProgram::class)
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

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters)) {
            ksort($filters);
        }

        return $query
            ->when($filters['organization_id'] ?? null, fn ($q, $orgId) =>
                $q->where('organization_id', $orgId)
            )
            ->when($filters['status'] ?? null, fn ($q, $status) =>
                $q->where('status', $status)
            )
            ->when($filters['created_from'] ?? null, fn ($q, $date) =>
                $q->whereDate('created_at', '>=', $date)
            )
            ->when($filters['created_to'] ?? null, fn ($q, $date) =>
                $q->whereDate('created_at', '<=', $date)
            )
            ->when(($filters['with_deleted'] ?? false) === true, fn ($q) =>
                $q->withTrashed()
            );
    }

    protected $casts = [
        'required_budget' => 'float',
        'total_funded_amount' => 'float',
    ];


}
