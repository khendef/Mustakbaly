<?php

namespace Modules\OrganizationsModule\Services\V1;

use Illuminate\Support\Facades\Log;
use Illuminate\Container\Attributes\DB;
use Modules\OrganizationsModule\Models\Organization;
/**
 * Service class for managing organizations.
 */
class OrganizationService
{
    public function getAll()
    {
        try {
       return Organization::with('media')->latest()->paginate(15);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve organizations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }

    }

    public function find(Organization $organization) : Organization
    {
        try {
            return $organization->load('media','programs:id,organization_id,name');
        } catch (\Exception $e) {
            Log::error('Failed to find organization', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }

    }

    public function create(array $data) : Organization
    {
        try {
            $organization = Organization::create($data);
            if (isset($data['logo'])) {
                    $organization->addMedia($data['logo'])
                        ->toMediaCollection('logo');
                }

            return $organization;
        } catch (\Exception $e) {
            Log::error('Failed to create organization', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function update(Organization $organization, array $data) : Organization
    {
        try {
            $organization->update($data);
            if (isset($data['logo'])) {
                    $organization->addMedia($data['logo'])
                        ->toMediaCollection('logo');
                }
            return $organization;
        } catch (\Exception $e) {
            Log::error('Failed to update organization', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function delete(Organization $organization)
    {
        try {
            return $organization->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete organization', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
    }
}
}
