<?php

namespace Modules\OrganizationsModule\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\OrganizationsModule\Models\Donor;

class DonorObserver
{
    private const CACHE_TIMESTAMP_KEY = 'donors:last_updated_at';

    protected function invalidatePaginationCache(): void
    {
        Cache::forever(
            self::CACHE_TIMESTAMP_KEY,
            now()->timestamp
        );
    }

    public function created(Donor $donor): void
    {
        $this->invalidatePaginationCache();
    }

    public function updated(Donor $donor): void
    {
        $this->invalidatePaginationCache();
    }

    public function deleted(Donor $donor): void
    {
        $this->invalidatePaginationCache();
    }
}
