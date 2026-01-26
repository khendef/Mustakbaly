<?php
namespace Modules\OrganizationsModule\Services\V1;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Modules\OrganizationsModule\Models\Donor;
use Modules\OrganizationsModule\Repositories\DonorRepository;

class DonorService
{
    private const TAG_GLOBAL = 'donors';
    private const TAG_PREFIX_DONOR = 'donor_';
    private const CACHE_TTL = 3600;

    public function getDonors(array $filters = [])
    {
        try{
        if (!empty($filters)) {
            ksort($filters);
        }

        $cacheKey = 'list:' . md5(json_encode($filters));

        return Cache::tags([self::TAG_GLOBAL])
            ->remember($cacheKey, self::CACHE_TTL, fn () =>
                Donor::query()
                    ->filter($filters)
                    ->orderByDesc('created_at')
                    ->get()
            );
            } catch (\Exception $e) {
            Log::error('retrive donor failed', [
                'filters' => $filters,
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    public function getDonorById(int $donorId): Donor
    {
        try{
        $cacheKey = self::TAG_PREFIX_DONOR . $donorId;

        return Cache::tags([
                self::TAG_GLOBAL,
                self::TAG_PREFIX_DONOR . $donorId,
            ])
            ->remember($cacheKey, self::CACHE_TTL, fn () =>
                Donor::findOrFail($donorId)
            );
            } catch (\Exception $e) {
            Log::error('retrive donor failed', [
                'donor_id' => $donorId,
                'exception' => $e,
            ]);
             throw $e;
        }
    }

        public function create(array $data): Donor
    {
        try{
        $donor = Donor::create($data);

        Cache::tags([self::TAG_GLOBAL])->flush();

        return $donor;
        }catch (\Exception $e) {
            Log::error('Create donor failed', [
                'payload' => $data,
                'exception' => $e,
            ]);

            throw $e;
        }
    }
public function update(Donor $donor, array $data): Donor
    {
        try{
        $donor->update($data);

        Cache::tags([
            self::TAG_GLOBAL,
            self::TAG_PREFIX_DONOR . $donor->id,
        ])->flush();

        return $donor->refresh();
                } catch (\Exception $e) {
            Log::error('update donor failed', [
                'donor_id' => $donor->id,
                'payload' => $data,
                'exception' => $e,
            ]);

            throw $e;
        }


    }

    public function delete(Donor $donor): void
    {
        try{
        $donor->delete();

        Cache::tags([
            self::TAG_GLOBAL,
            self::TAG_PREFIX_DONOR . $donor->id,
        ])->flush();
                } catch (\Exception $e) {
            Log::error('delete donor failed', [
                'donor_id' => $donor->id,
                'exception' => $e,
            ]);

            throw $e;
        }

    }
}
