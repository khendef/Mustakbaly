<?php

namespace Modules\OrganizationsModule\Models;

use Illuminate\Support\Facades\Cache;
use Modules\OrganizationsModule\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DonorProgram extends Pivot
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
        'contribution_amount' => MoneyCast::class,
    ];
}
