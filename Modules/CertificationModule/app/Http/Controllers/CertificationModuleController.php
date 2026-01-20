<?php

namespace Modules\CertificationModule\Http\Controllers;
use App\Http\Controllers\Controller;
use Modules\CertificationModule\Models\Certificate;
use Modules\CertificationModule\Services\CertificateService;
use Modules\CertificationModule\Repositories\CertificateRepository;
use Modules\CertificationModule\Http\Requests\StoreCertificateRequest;
use Modules\CertificationModule\Http\Requests\CertificateFilterRequest;
use Modules\CertificationModule\Http\Requests\UpdateCertificateRequest;

class CertificationModuleController extends Controller
{
public function __construct(
        protected CertificateRepository $repository,
        protected CertificateService $service
    ) {}

public function index(CertificateFilterRequest $request)
{
    return self::success(
        $this->repository->paginateFiltered(
            $request->validated()
        ),
        'Certificates retrieved successfully',
        200
    );
}

    public function store(StoreCertificateRequest $request)
    {
        return self::success(
            $this->service->create($request->validated()),
            'Certificate issued successfully',
            201
        );
    }

    public function update(
        UpdateCertificateRequest $request,
        Certificate $certificate
    ) {
        return self::success(
            $this->service->update(
                $certificate,
                $request->validated()
            ),
            'Certificate updated successfully',
            200
        );
    }

    public function destroy(Certificate $certificate)
    {
        $this->service->delete($certificate);

        return self::success(
            null,
            'Certificate revoked successfully',
            200
        );
    }
}
