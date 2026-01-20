<?php
namespace Modules\OrganizationsModule\Repositories;
use Illuminate\Support\Facades\Cache;
use Modules\OrganizationsModule\Models\Program;
use Modules\OrganizationsModule\Filters\ProgramFilter;

class ProgramRepository
{
      private const PAGINATION_VERSION = 'programs:pagination:version';

    public function paginateFiltered(
        array $filters,
        int $perPage = 15
    ) {
        $page = request('page', 1);
        $version = Cache::get(self::PAGINATION_VERSION, 1);

        return Cache::remember(
            "programs:pagination:v{$version}:{$perPage}:{$page}:" . md5(json_encode($filters)),
            600,
            function () use ($filters, $perPage) {
                $query = Program::query()->with('organization');

                $query = app(ProgramFilter::class)
                    ->apply($query, $filters);

                return $query->latest()->paginate($perPage);
            }
        );
    }

    public function clearProgram(int $id): void
    {
        Cache::forget("programs:{$id}");
    }

    public function bumpPagination(): void
    {
        Cache::increment(self::PAGINATION_VERSION);
    }
}
