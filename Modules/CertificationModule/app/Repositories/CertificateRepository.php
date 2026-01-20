<?php
namespace Modules\CertificationModule\Repositories;
use Illuminate\Support\Facades\Cache;
use Modules\CertificationModule\Models\Certificate;
use Modules\CertificationModule\Filters\CertificateFilter;

class CertificateRepository
{
    private const PAGINATION_VERSION = 'certificates:pagination:version';


    public function paginateFiltered(
        array $filters,
        int $perPage = 15
    ) {
        $page = request('page', 1);
        $version = Cache::get(self::PAGINATION_VERSION, 1);

        return Cache::remember(
            "certificates:pagination:v{$version}:{$perPage}:{$page}:" . md5(json_encode($filters)),
            600,
            function () use ($filters, $perPage) {
                $query = Certificate::query()->with(['organization']);

                $query = app(CertificateFilter::class)
                    ->apply($query, $filters);

                return $query->latest()->paginate($perPage);
            }
        );
    }
    public function paginateCached(int $perPage = 15)
    {
        $page = request('page', 1);
        $version = Cache::get(self::PAGINATION_VERSION, 1);

        return Cache::remember(
            "certificates:pagination:v{$version}:{$perPage}:{$page}",
            600,
            fn () => Certificate::latest()->paginate($perPage)
        );
    }

    public function clearCertificateCache(int $id, string $number): void
    {
        Cache::forget("certificates:{$id}");
        Cache::forget("certificates:number:{$number}");
    }

    public function bumpPagination(): void
    {
        Cache::increment(self::PAGINATION_VERSION);
    }
}
