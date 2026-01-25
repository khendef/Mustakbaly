<?php

namespace Modules\UserManagementModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\UserManagementModule\Services\V1\UserService;
use Modules\UserManagementModule\Http\Requests\Api\V1\User\UserFilterRequest;
use Modules\UserManagementModule\Http\Requests\Api\V1\User\UserStoreRequest;
use Modules\UserManagementModule\Http\Requests\Api\V1\User\UserUpdateRequest;
use Modules\UserManagementModule\Models\User;

/**
 * this controller is only managed by super-admin
 * it is responsible for managing platform accounts for all users
 * listing users with their roles and permissions and the organizattion they belongs to
 * updating their account infomation , creating new accounts, or deleting an existing account 
 */
class UserController extends Controller
{
    protected UserService $userService;
    public function __construct(UserService $userService){
        $this->userService = $userService;

        $this->middleware('permission:list-users')->only('index');
        $this->middleware('permission:show-user')->only('show');
        $this->middleware('permission:create-user')->only('store');
        $this->middleware('permission:update-user')->only('update');
        $this->middleware('permission:delete-user')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(UserFilterRequest $request)
    {
        $filters = $request->validated();
        $users = $this->userService->list($filters);
        return self::paginate($users,'users retrieved successfully');
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        // we can assign role super admin or technical
        $user = $this->userService->create($request->validated());
        return self::success($user,'user created successfully',201);
    }

    /**
     * Show the specified resource.
     */
    public function show(int $id)
    {
        $user = $this->userService->findById($id);
        return self::success($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user)
    {
       $user = $this->userService->update($user,$request->validated());
       return self::success($user, 'user updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return self::success(null,'user deleted successfully');
    }
}
