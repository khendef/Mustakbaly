<?php
namespace Modules\OrganizationsModule\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Modules\OrganizationsModule\Http\Requests\V1\Donor\StoreDonorRequest;
use Modules\OrganizationsModule\Http\Requests\V1\Donor\UpdateDonorRequest;
use Modules\OrganizationsModule\Models\Donor;
use Modules\OrganizationsModule\Http\Requests\V1\Donor\DonorFilterRequest;
use Modules\OrganizationsModule\Services\V1\DonorService;

class DonorController extends Controller
{
    /**
     * Constructor to initialize DonorService.
     */
    public function __construct(protected DonorService $donorservice) 
    {
        $this->middleware('permission:list-donors')->only('index');
        $this->middleware('permission:show-donor')->only('show');
        $this->middleware('permission:create-donor')->only('store');
        $this->middleware('permission:update-donor')->only('update');
        $this->middleware('permission:delete-donor')->only('destroy');
    }


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
