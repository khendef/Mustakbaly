<?php
namespace Modules\OrganizationsModule\Services\V1;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Modules\OrganizationsModule\Models\Donor;
use Modules\OrganizationsModule\Models\Program;
use Modules\OrganizationsModule\Models\DonorProgram;
use Modules\OrganizationsModule\ValueObjects\Money;

class FundingService
{
    private const TAG_GLOBAL = 'funding';
    private const TAG_PREFIX_DONOR = 'donor:';
    private const TAG_PREFIX_PROGRAM = 'program:';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Add funding from a donor to a program
     *
     * @param Donor $donor
     * @param Program $program
     * @param Money $amount
     * @return void
     *
     * @throws \Throwable
     */
 public function fund(Donor $donor, Program $program, Money $amount): void
    {
        try {
            DB::transaction(function () use ($donor, $program, $amount) {

                // Find existing pivot or create a new one
                $pivot = DonorProgram::firstOrCreate(
                    [
                        'donor_id' => $donor->id,
                        'program_id' => $program->id,
                    ],
                    ['contribution_amount' => new Money(0, $amount->currency())]
                );

                // update contribution_amount by Money object
                $pivot->contribution_amount = $pivot->contribution_amount->add($amount);
                $pivot->save();

                // update total funded amount ]
                $program->total_funded_amount = $program->total_funded_amount->add($amount);
                $program->save();

                // flush cache
                Cache::tags([
                    self::TAG_GLOBAL,
                    self::TAG_PREFIX_DONOR . $donor->id,
                    self::TAG_PREFIX_PROGRAM . $program->id,
                ])->flush();
            });
          } catch (\Exception $e) {
            Log::error('Funding failed', [
                'donor_id' => $donor->id,
                'program_id' => $program->id,
                'amount' => (string)$amount,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 8f82310be1ed3956233161a9a739ff5b62ca6e3c
