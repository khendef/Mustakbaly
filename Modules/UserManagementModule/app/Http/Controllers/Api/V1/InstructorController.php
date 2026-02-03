<?php

namespace Modules\UserManagementModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\UserManagementModule\DTOs\InstructorDTO;
use Modules\UserManagementModule\Models\User;
use Modules\UserManagementModule\Services\V1\InstructorService;
use Modules\UserManagementModule\Http\Requests\Api\V1\Instructor\InstructorFilterRequest;
use Modules\UserManagementModule\Http\Requests\Api\V1\Instructor\InstructorStoreRequest;
use Modules\UserManagementModule\Http\Requests\Api\V1\Instructor\InstructorUpdateRequest;
use Modules\UserManagementModule\Models\Instructor;

class InstructorController extends Controller
{

    protected InstructorService $instructorService;

    public function __construct(InstructorService $instructorService)
    {
        $this->instructorService = $instructorService;

        $this->middleware('permission:list-instructors')->only('index');
        $this->middleware('permission:show-instructor')->only('show');
        $this->middleware('permission:create-instructor')->only('store');
        $this->middleware('permission:update-instructor')->only('update');
        $this->middleware('permission:delete-instructor')->only('destroy');
    }


    public function index(InstructorFilterRequest $request)
    {
        $instructors = $this->instructorService->list($request->validated());
        return self::paginated($instructors, 'instructors retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InstructorStoreRequest $request)
    {
        $instructorDTO = InstructorDTO::fromArray($request->validated());
        $instructor = $this->instructorService->create($instructorDTO);
        return self::success($instructor, 'instructor created successfuly', 201);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $instructor = $this->instructorService->findById($id);
        return self::success($instructor);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InstructorUpdateRequest $request, User $instructor)
    {
        $instructorDTO = InstructorDTO::fromArray($request->validated());
        $instructor = $this->instructorService->update($instructor, $instructorDTO);
        return self::success($instructor, 'instructor updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $instructor)
    {
        $this->instructorService->delete($instructor);
        return self::success(null, 'instructor deleted successfully');
    }
}
