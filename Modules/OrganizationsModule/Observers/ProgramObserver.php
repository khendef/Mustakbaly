<?php
namespace Modules\OrganizationsModule\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\OrganizationsModule\Models\Program;

class ProgramObserver
{
    private const CACHE_TIMESTAMP_KEY = 'programs:last_updated_at';

    protected function invalidatePaginationCache(): void
    {
        Cache::forever(
            self::CACHE_TIMESTAMP_KEY,
            now()->timestamp
        );
    }

    public function created(Program $program): void
    {
        $this->invalidatePaginationCache();
    }

    public function updated(Program $program): void
    {
        $this->invalidatePaginationCache();
    }

    public function deleted(Program $program): void
    {
        $this->invalidatePaginationCache();
    }
}
