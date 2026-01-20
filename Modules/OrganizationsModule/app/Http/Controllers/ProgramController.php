<?php
namespace Modules\OrganizationsModule\Http\Controllers;
use App\Http\Controllers\Controller;
use Modules\OrganizationsModule\Models\Program;
use Modules\OrganizationsModule\Models\Organization;

use Modules\OrganizationsModule\Services\ProgramService;
use Modules\OrganizationsModule\Http\Requests\ProgramRequest;
use Modules\OrganizationsModule\Repositories\ProgramRepository;
use Modules\OrganizationsModule\Http\Requests\StoreProgramRequest;
use Modules\OrganizationsModule\Http\Requests\ProgramFilterRequest;
use Modules\OrganizationsModule\Http\Requests\UpdateProgramRequest;
/**
 * Controller for managing programs.
 */
class ProgramController extends Controller
{
    /**
     * Constructor to initialize ProgramService.
     */
    public function __construct(
        protected ProgramRepository $programrepository,
        protected ProgramService $programservice
    ) {}

public function index(ProgramFilterRequest $request)
{
    return self::success(
        $this->programrepository->paginateFiltered(
            $request->validated()
        ),
        'Programs retrieved successfully',
        200
    );
}

//create program

    public function store(ProgramRequest $request)
    {
        return self::success(
            $this->programservice->create($request->validated()),
            'Program created successfully',
            201
        );
    }
//update program

    public function update(ProgramRequest $request, Program $program)
    {
        return self::success(
            $this->programservice->update($program, $request->validated()),
            'Program updated successfully',
            200
        );
    }
    /* ================= DELETE ================= */

    public function destroy(Program $program)
    {
        $this->programservice->delete($program);
        return self::success(null, 'Program deleted successfully', 200);
    }
}
