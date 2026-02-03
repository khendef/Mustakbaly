<?php

namespace Modules\UserManagementModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use \Modules\UserManagementModule\Services\V1\PermissionService;

class PermissionController extends Controller
{
    protected PermissionService $permissionService;
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
        $this->middleware('permission:list-permissions')->only('index');
    }

    
    
    public function index()
    {
        $permissions = $this->permissionService->getAllPermissions();
        return self::success($permissions);
    }

}
