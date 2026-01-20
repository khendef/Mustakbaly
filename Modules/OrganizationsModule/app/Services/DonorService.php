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
            $donor = Donor::create($data);
            $this->repository->bumpPagination();
            return $donor;
        });
    }

    public function update(Donor $donor, array $data): Donor
    {
        return DB::transaction(function () use ($donor, $data) {
            $donor->update($data);
            $this->repository->clearDonorCache($donor->id);
            $this->repository->bumpPagination();
            return $donor->refresh();
        });
    }

    public function delete(Donor $donor): void
    {
        DB::transaction(function () use ($donor) {
            $this->repository->clearDonorCache($donor->id);
            $this->repository->bumpPagination();
            $donor->delete();
        });
    }
}
