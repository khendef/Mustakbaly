<?php
namespace Modules\OrganizationsModule\Repositories;
use Illuminate\Support\Facades\Cache;
use Modules\OrganizationsModule\Models\Donor;

class DonorRepository
{
    private const PAGINATION_VERSION = 'donors:pagination:version';

    public function findCached(int $id): Donor
    {
        return Cache::remember(
            "donors:{$id}",
            1800,
            fn () => Donor::with('user')->findOrFail($id)
        );
    }

    public function paginateCached(int $perPage = 15)
    {
        $page = request('page', 1);
        $version = Cache::get(self::PAGINATION_VERSION, 1);

        return Cache::remember(
            "donors:pagination:v{$version}:{$perPage}:{$page}",
            600,
            fn () => Donor::with('user')->paginate($perPage)
        );
    }
      public function clearDonorCache(int $id): void
    {
        Cache::forget("donors:{$id}");
        Cache::forget("donors:{$id}:total_donated");
    }

    public function bumpPagination(): void
    {
        Cache::increment(self::PAGINATION_VERSION);
    }
}
