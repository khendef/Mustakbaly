<?php
namespace Modules\OrganizationsModule\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Modules\OrganizationsModule\Models\Donor;

use Modules\OrganizationsModule\Services\DonorService;
use Modules\OrganizationsModule\Http\Requests\StoreDonorRequest;
use Modules\OrganizationsModule\Http\Requests\UpdateDonorRequest;
use Modules\OrganizationsModule\Http\Requests\V1\Donor\DonorFilterRequest;

class DonorController extends Controller
{
    /**
     * Constructor to initialize DonorService.
     */
    public function __construct(
        protected DonorService $donorservice
    ) {}


    /**
     * Display a listing of the resource.
     */
    public function index(DonorFilterRequest $request)
    {
        $donors = $this->donorservice->getDonors($request->filters());
        return self::success($donors, 'Donors retrieved successfully.', 200);
    }

        /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $donor =  $this->donorservice->getDonorById($id);
        return self::success($donor, 'Donor retrieved successfully.', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDonorRequest $request)
    {
        $donor = $this->donorservice->create($request->validated());
        return self::success($donor, 'Donor created successfully.', 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDonorRequest $request, Donor $donor)
    {
        $donor = $this->donorservice->update($donor, $request->validated());
        return self::success($donor, 'Donor updated successfully.', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Donor $donor)
    {
        $this->donorservice->delete($donor);
        return self::success(null, 'Donor deleted successfully.', 200);
    }
}
