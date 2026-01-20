<?php
namespace Modules\OrganizationsModule\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\OrganizationsModule\Models\Donor;
use Modules\OrganizationsModule\Models\Funding;
use Modules\OrganizationsModule\Models\Program;
use Modules\OrganizationsModule\Models\DonorProgram;

class FundingService
{
public function fund(
        Donor $donor,
        Program $program,
        float $amount
    ): void {

        DB::transaction(function () use ($donor, $program, $amount) {

            $pivot = DonorProgram::firstOrCreate(
                [
                    'donor_id' => $donor->id,
                    'program_id' => $program->id,
                ],
                ['contribution_amount' => 0]
            );

            $pivot->increment('contribution_amount', $amount);

            Program::where('id', $program->id)
                ->increment('total_funded_amount', $amount);

            Cache::forget("donors:{$donor->id}:total_donated");
            Cache::forget("programs:{$program->id}");
        });
    }
}
