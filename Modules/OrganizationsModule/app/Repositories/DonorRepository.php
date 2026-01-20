<?php
namespace Modules\OrganizationsModule\Repositories;
use Illuminate\Support\Facades\Cache;
use Modules\OrganizationsModule\Models\Donor;
class DonorRepository

{
    private const CACHE_TIMESTAMP_KEY = 'donors:last_updated_at';

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
        $timestamp = Cache::get(self::CACHE_TIMESTAMP_KEY, 'init');

        $cacheKey = sprintf(
            'donors:pagination:%s:%d:%d',
            $timestamp,
            $perPage,
            $page
        );

        return Cache::remember(
            $cacheKey,
            600,
            fn () => Donor::with('user')->paginate($perPage)
        );
    }

    public function clearDonorCache(int $id): void
    {
        Cache::forget("donors:{$id}");
        Cache::forget("donors:{$id}:total_donated");
    }
}
