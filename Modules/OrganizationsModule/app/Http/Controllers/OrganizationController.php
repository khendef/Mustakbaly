<?php

namespace Modules\OrganizationsModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\OrganizationsModule\Http\Requests\StoreOrganizationRequest;
use Modules\OrganizationsModule\Http\Requests\UpdateOrganizationRequest;

use Modules\OrganizationsModule\Models\Organization;
use Modules\OrganizationsModule\Services\OrganizationService;

class OrganizationController extends Controller
{
/**
     * Constructor to initialize OrganizationService.
     */
    public function __construct(private OrganizationService $organizationService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organizations = $this->organizationService->getAll();
       return self::success($organizations ,'Organizations retrieved successfully.',200);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function store(StoreOrganizationRequest $request , OrganizationService $organizationService)
    {
        $organization = $organizationService->create($request->validated());
        return self::success($organization ,'Organization created successfully.',201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        return self::success($organization = $this->organizationService->find($organization) ,'Organization retrieved successfully.',200);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization)
    {
        $organization = $this->organizationService->update($organization, $request->validated());
        return self::success($organization, 'Organization updated successfully.', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        return self::success($this->organizationService->delete($organization), 'Organization deleted successfully.', 200);
    }
}
