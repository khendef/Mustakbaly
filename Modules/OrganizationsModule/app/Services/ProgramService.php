<?php
namespace Modules\OrganizationsModule\Services;
use Illuminate\Support\Facades\Log;
use Illuminate\Container\Attributes\DB;

use Modules\OrganizationsModule\Models\Program;
use Modules\OrganizationsModule\Models\Organization;
use Modules\OrganizationsModule\Repositories\ProgramRepository;
/**
 * Service class for managing programs.
 */
class ProgramService
{
    public function __construct(
        protected ProgramRepository $repository
    ) {}

    public function create(array $data): Program
    {
        return DB::transaction(function () use ($data) {
            return Program::create($data);
        });
    }

    public function update(Program $program, array $data): Program
    {
        return DB::transaction(function () use ($program, $data) {
            $program->update($data);
            return $program->refresh();
        });
    }

    public function delete(Program $program): void
    {
        DB::transaction(function () use ($program) {
            $program->delete();
        });
    }
}
