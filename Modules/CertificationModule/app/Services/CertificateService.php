<?php
namespace Modules\CertificationModule\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Container\Attributes\Log;
use Modules\CertificationModule\Models\Certificate;
/**
 * Service class for managing certificates.
 */
class CertificateService
{
    private const TAG_GLOBAL = 'certificates';
    private const TAG_PREFIX_CERTIFICATE = 'certificate_';
    private const CACHE_TTL = 3600;

    /**
     * Get certificates list with filters
     */
    public function getCertificates(array $filters = [])
    {
        try {
            ksort($filters);

            $cacheKey = 'list:' . md5(json_encode($filters));

            return Cache::tags([self::TAG_GLOBAL])
                ->remember($cacheKey, self::CACHE_TTL, fn () =>
                    Certificate::query()
                        ->filter($filters)
                        ->orderByDesc('issue_date')
                        ->get()
                );

        } catch (\Throwable $e) {
            Log::error('Failed to fetch certificates', [
                'filters' => $filters,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Get certificate by ID
     */
    public function getById(int $id): Certificate
    {
        try {
            return Cache::tags([
                self::TAG_GLOBAL,
                self::TAG_PREFIX_CERTIFICATE . $id,
            ])->remember(
                self::TAG_PREFIX_CERTIFICATE . $id,
                self::CACHE_TTL,
                fn () => Certificate::findOrFail($id)
            );

        } catch (\Exception $e) {
            Log::error('Failed to fetch certificate', [
                'certificate_id' => $id,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    public function create(array $data): Certificate
    {
        try {
            $certificate = Certificate::create($data);

            Cache::tags([self::TAG_GLOBAL])->flush();

            return $certificate;

        } catch (\Exception $e) {
            Log::error('Failed to create certificate', [
                'data' => $data,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    public function update(Certificate $certificate, array $data): Certificate
    {
        try {
            $certificate->update($data);

            Cache::tags([
                self::TAG_GLOBAL,
                self::TAG_PREFIX_CERTIFICATE . $certificate->id,
            ])->flush();

            return $certificate->refresh();

        } catch (\Exception $e) {
            Log::error('Failed to update certificate', [
                'certificate_id' => $certificate->id,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    public function delete(Certificate $certificate): void
    {
        try {
            $certificate->delete();

            Cache::tags([
                self::TAG_GLOBAL,
                self::TAG_PREFIX_CERTIFICATE . $certificate->id,
            ])->flush();

        } catch (\Exception $e) {
            Log::error('Failed to delete certificate', [
                'certificate_id' => $certificate->id,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
