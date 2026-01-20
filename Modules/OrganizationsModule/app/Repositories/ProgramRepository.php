<?php
namespace Modules\OrganizationsModule\Repositories;
use Illuminate\Support\Facades\Cache;
use Modules\OrganizationsModule\Models\Program;
use Modules\OrganizationsModule\Filters\ProgramFilter;
/**
 * Repository for managing Program data.
 * @package Modules\OrganizationsModule\Repositories
 */
class ProgramRepository
{
    private const CACHE_TIMESTAMP_KEY = 'programs:last_updated_at';
public function paginateFiltered(array $filters, int $perPage = 15)
    {
        $page = request('page', 1);
        $timestamp = Cache::get(self::CACHE_TIMESTAMP_KEY, 'init');

        $cacheKey = sprintf(
            'programs:pagination:%s:%s:%d:%d',
            $timestamp,
            md5(json_encode($filters)),
            $perPage,
            $page
        );

        return Cache::remember($cacheKey, 600, function () use ($filters, $perPage) {

            $query = Program::query()->with('organization');

            $query = app(ProgramFilter::class)
                ->apply($query, $filters);

            return $query->latest()->paginate($perPage);
        });
    }

    public function clearProgram(int $id): void
    {
        Cache::forget("programs:{$id}");
    }
    }
