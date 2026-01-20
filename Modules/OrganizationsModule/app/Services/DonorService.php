<?php
namespace Modules\OrganizationsModule\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\OrganizationsModule\Models\Donor;
use Modules\OrganizationsModule\Repositories\DonorRepository;

class DonorService
{
    public function __construct(
        protected DonorRepository $repository
    ) {}

    public function create(array $data): Donor
    {
        return DB::transaction(function () use ($data) {
            return Donor::create($data);
        });
    }

    public function update(Donor $donor, array $data): Donor
    {
        return DB::transaction(function () use ($donor, $data) {
            $donor->update($data);
            return $donor->refresh();
        });
    }

    public function delete(Donor $donor): void
    {
        DB::transaction(function () use ($donor) {
            $donor->delete();
        });
    }
}
