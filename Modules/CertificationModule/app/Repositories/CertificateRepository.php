<?php
namespace Modules\CertificationModule\Repositories;
use Illuminate\Support\Facades\Cache;
use Modules\CertificationModule\Models\Certificate;
use Modules\CertificationModule\Filters\CertificateFilter;

class CertificateRepository
{
    private const CACHE_TIMESTAMP_KEY = 'certificates:last_updated_at';

    public function paginateFiltered(array $filters, int $perPage = 15)
    {
        $page = request('page', 1);
        $timestamp = Cache::get(self::CACHE_TIMESTAMP_KEY, 'init');

        $cacheKey = sprintf(
            'certificates:pagination:%s:%s:%d:%d',
            $timestamp,
            md5(json_encode($filters)),
            $perPage,
            $page
        );

        return Cache::remember($cacheKey, 600, function () use ($filters, $perPage) {

            $query = Certificate::query()->with(['organization']);

            $query = app(CertificateFilter::class)
                ->apply($query, $filters);

            return $query->latest()->paginate($perPage);
        });
    }

    public function paginateCached(int $perPage = 15)
    {
        $page = request('page', 1);
        $timestamp = Cache::get(self::CACHE_TIMESTAMP_KEY, 'init');

        $cacheKey = sprintf(
            'certificates:pagination:%s:%d:%d',
            $timestamp,
            $perPage,
            $page
        );

        return Cache::remember(
            $cacheKey,
            600,
            fn () => Certificate::latest()->paginate($perPage)
        );
    }

    public function clearCertificateCache(int $id, string $number): void
    {
        Cache::forget("certificates:{$id}");
        Cache::forget("certificates:number:{$number}");
    }
}
