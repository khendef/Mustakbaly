<?php
namespace Modules\UserManagementModule\App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;



/**
 * custom query builder for users model 
 * by using custom builder we ensure the model and controller are clean 
 * filters are reusable and model is clear of scopes
 */
class UserBuilder extends Builder
{
    //GET /admin/users?roles[]=teacher&roles[]=manager
    public function byRole(array $roles):self
    {
        return $this->role($roles);
    }

    public function search(string $term)
    {
        return $this->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%");
        });
    }

    public function gender(string $gender)
    {
        return $this->where('gender',$gender);
    }

    public function inOrganization(int $organiztionId)
    {
        return $this->whereHas('organizations', function($q) use ($organiztionId) {
            $q->where('organizations.id', $organiztionId);
        });
    }
}