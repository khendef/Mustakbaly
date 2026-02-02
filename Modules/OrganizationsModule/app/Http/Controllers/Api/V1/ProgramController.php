<?php
namespace Modules\OrganizationsModule\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Modules\OrganizationsModule\Models\Program;

use Modules\OrganizationsModule\Services\V1\ProgramService;
use Modules\OrganizationsModule\Repositories\ProgramRepository;
use Modules\OrganizationsModule\Http\Requests\V1\Program\StoreProgramRequest;
use Modules\OrganizationsModule\Http\RequestsV1\Program\ProgramFilterRequest;
use Modules\OrganizationsModule\Http\Requests\V1\Program\UpdateProgramRequest;
/**
 * Controller for managing programs.
 */
class ProgramController extends Controller
{
    /**
     * Constructor to initialize ProgramService.
     */
    public function __construct(
        protected ProgramService $programservice
    ) {}

public function index(ProgramFilterRequest $request)
{
    return self::success(
        $this->programservice->getPrograms($request->filters()),
        'Programs retrieved successfully',
        200
    );
}

    /**
     * Show single program
     */
    public function show(int $id)
    {
        return self::success($this->programservice->getProgramById($id), 'Program retrieved successfully',200);

    }

    /**
     * Store program
     */
    public function store(StoreProgramRequest $request)
    {
        return self::success(
            $this->programservice->create($request->validated()),
            'Program created successfully',
            201
        );
    }
    /**
     * Update program
     */
    public function update(UpdateProgramRequest $request, Program $program)
    {
        return self::success(
            $this->programservice->update($program, $request->validated()),
            'Program updated successfully',
            200
        );
    }
     /**
     * Delete program (soft delete)
     */
    public function destroy(Program $program)
    {
        $this->programservice->delete($program);
        return self::success(null, 'Program deleted successfully', 200);
    }
}
