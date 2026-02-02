<?php

namespace Modules\OrganizationsModule\Services\V1;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\OrganizationsModule\Models\Organization;
use Modules\UserManagementModule\Models\User;
/**
 * Service class for managing organizations.
 */
class OrganizationService
{
    public function getAll()
    {
        try {
        return Organization::latest()->paginate(15);
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
            return $organization->load('programs:id,organization_id,title');
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


    /**
     * logic:
     * 1. create or find user
     * 2. assign role manager
     * 3. attach to organization
     * 
     * Summary of assignManager
     * @param Organization $organization
     * @param array $data
     * @return User
     */   
    public function assignManager(Organization $organization , array $data)
    {
        return DB::transaction(function() use($data, $organization ) {
            if(isset($data['user_id'])) {
                $user = User::find($data['user_id']);
            }
            else{
                
                $user = User::Create($data); 
            }
            $user->assignRole('manager');      
            $organization->users()->syncWithoutDetaching([$user->id => ['role' => 'manager']]);
            return $user;
        });
    }
}
