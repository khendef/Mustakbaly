<?php
namespace Modules\UserManagementModule\Models\Builders;

use Illuminate\Database\Eloquent\Builder;


class InstructorBuilder extends Builder
{
    public function search(string $term)
    {
        return $this->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%")
                    ->orWhere('specialization', 'LIKE', "%{$term}%");
        });
    }

    public function experience(int $years)
    {
        return $this->where('years_of_experience',$years);
    }

    public function inOrganization(int $organiztionId)
    {
        return $this->whereHas('organizations', function($q) use ($organiztionId) {
            $q->where('organizations.id', $organiztionId);
        });
    }
}