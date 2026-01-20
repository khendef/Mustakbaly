<?php
namespace Modules\CertificationModule\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Attributes\Log;
use Modules\CertificationModule\Models\Certificate;
use Modules\CertificationModule\Repositories\CertificateRepository;
/**
 * Service class for managing certificates.
 */
class CertificateService
{
    public function __construct(
        protected CertificateRepository $repository
    ) {}

    public function create(array $data): Certificate
    {
        return DB::transaction(function () use ($data) {

            $data['certificate_number'] ??=
                $this->generateCertificateNumber();

            return Certificate::create($data);
        });
    }

    public function update(
        Certificate $certificate,
        array $data
    ): Certificate {

        return DB::transaction(function () use ($certificate, $data) {

            $certificate->update($data);

            return $certificate->refresh();
        });
    }

    public function delete(Certificate $certificate): void
    {
        DB::transaction(function () use ($certificate) {
            $certificate->delete();
        });
    }

    private function generateCertificateNumber(): string
    {
        return 'CERT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}
