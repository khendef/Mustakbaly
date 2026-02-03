<?php

namespace Modules\UserManagementModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\UserManagementModule\DTOs\AuditorDTO;
use Modules\UserManagementModule\Models\User;
use Modules\UserManagementModule\Services\V1\AuditorService;
use Modules\UserManagementModule\Http\Requests\Api\V1\Auditor\AuditorFilterRequest;
use Modules\UserManagementModule\Http\Requests\Api\V1\Auditor\AuditorStoreRequest;
use Modules\UserManagementModule\Http\Requests\Api\V1\Auditor\AuditorUpdateRequest;
use Modules\UserManagementModule\Models\Auditor;

class AuditorController extends Controller
{
    protected AuditorService $auditorService;

    public function __construct(AuditorService $auditorService)
    {
        $this->auditorService = $auditorService;

        $this->middleware('permission:list-auditors')->only('index');
        $this->middleware('permission:show-auditor')->only('show');
        $this->middleware('permission:create-auditor')->only('store');
        $this->middleware('permission:update-auditor')->only('update');
        $this->middleware('permission:delete-auditor')->only('destroy');
    }


    public function index(AuditorFilterRequest $request)
    {
        $auditors = $this->auditorService->list($request->validated());
        return self::paginated($auditors, 'auditors retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AuditorStoreRequest $request)
    {
        $auditorDTO = AuditorDTO::fromArray($request->validated());

        $auditor = $this->auditorService->create($auditorDTO);
        return self::success($auditor, 'auditor created successfully', 201);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {

        $auditor = $this->auditorService->findById($id);
        return self::success($auditor, 'auditor retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AuditorUpdateRequest $request, User $auditor)
    {
        $auditorDTO = AuditorDTO::fromArray($request->validated());
        $auditor = $this->auditorService->update($auditor, $auditorDTO);
        return self::success($auditor, 'auditor update successfully',);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $auditor)
    {
        $this->auditorService->delete($auditor);
        return self::success(null, 'auditor deleted successfully');
    }
}
