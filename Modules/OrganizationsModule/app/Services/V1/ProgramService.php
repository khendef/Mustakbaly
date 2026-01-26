<?php
namespace Modules\OrganizationsModule\Services\V1;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\OrganizationsModule\Models\Program;

/**
 * Service class for managing Program.
 * Handles Database Transactions, Caching Strategies, and Business Logic.
 */
class ProgramService
{
    /**
     * The global cache tag for the events list.
     */
    private const TAG_GLOBAL = 'programs';
    /**
     * The prefix for individual event cache tags.
     */
    private const TAG_PREFIX_PROGRAM = 'program_';
    /**
     * Cache Time-To-Live in seconds (1 Hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Get programs with filters (cached)
     */
    public function getPrograms(array $filters,int $perPage = 15) : LengthAwarePaginator
    {
        // ensure stable key generation
        if (!empty($filters)) {
            ksort($filters);
        }
        $cacheKey = 'list:' . md5(json_encode($filters));

        return Cache::tags([self::TAG_GLOBAL])
            ->remember($cacheKey, self::CACHE_TTL, fn () =>
                Program::query()
                    ->filter($filters)
                    ->orderByDesc('created_at')
                    ->paginate($perPage)
            );
    }

    /**
     * Get single program by ID (cached)
     */
    public function getProgramById(int $programId)
    {
        $cacheKey = self::TAG_PREFIX_PROGRAM . $programId;

        return Cache::tags([
                self::TAG_GLOBAL,
                self::TAG_PREFIX_PROGRAM . $programId,
            ])
            ->remember($cacheKey, self::CACHE_TTL, function () use ($programId) {
                return Program::findOrFail($programId);
            });
    }

    /**
     * Create program and invalidate cache
     */
    public function create(array $data): Program
    {
        $program = Program::create($data);

        $this->flushGlobalCache();

        return $program;
    }

    /**
     * Update program and invalidate cache
     */
    public function update(Program $program, array $data): Program
    {
        $program->update($data);

        $this->flushProgramCache($program->id);

        return $program->refresh();
    }

    /**
     * Soft delete program
     */
    public function delete(Program $program): void
    {
        $program->delete();

        $this->flushProgramCache($program->id);
    }

    /**
     * Restore program
     */
    public function restore(int $programId): void
    {
        Program::withTrashed()
            ->findOrFail($programId)
            ->restore();

        $this->flushProgramCache($programId);
    }

    /**
     * ------------------------
     * Cache helpers
     * ------------------------
     */

    private function makeCacheKey(string $prefix, array $params = []): string
    {
        if (!empty($params)) {
            ksort($params);
        }

        return $prefix . '_' . md5(json_encode($params));
    }

    private function flushProgramCache(int $programId): void
    {
        Cache::tags([
            self::TAG_GLOBAL,
            self::TAG_PREFIX_PROGRAM . $programId,
        ])->flush();
    }

    private function flushGlobalCache(): void
    {
        Cache::tags([self::TAG_GLOBAL])->flush();
    }
}
