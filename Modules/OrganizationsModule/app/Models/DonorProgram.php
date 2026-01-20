<?php
namespace Modules\OrganizationsModule\Models;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class DonorProgram extends Model
{
    protected $table = 'donor_program';

    protected $fillable = [
        'donor_id',
        'program_id',
        'contribution_amount',
    ];

    // Relationship with Donor
    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    // Relationship with Program
    public function program()
    {
        return $this->belongsTo(Program::class);
    }


    protected $casts = [
        'contribution_amount' => 'float',
    ];

        /* ================= Mutators ================= */

    public function setContributionAmountAttribute($value)
    {
        $this->attributes['contribution_amount'] = max(0, (float) $value);
    }
    /* ================= Events ================= */

    protected static function booted()
    {
        static::saved(fn ($pivot) => $pivot->invalidateCaches());
        static::deleted(fn ($pivot) => $pivot->invalidateCaches());
    }

    private function invalidateCaches(): void
    {
        Cache::forget("donor:{$this->donor_id}:total_donated");
        Cache::forget("program:{$this->program_id}");
    }
}
