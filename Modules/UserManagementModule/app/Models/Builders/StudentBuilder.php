<?php
namespace Modules\UserManagementModule\App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;


class StudentBuilder extends Builder
{
    public function search(string $term)
    {
        return $this->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('email', 'LIKE', "%{$term}%");
        });
    }

    public function byEducation(array $levels)
    {
        return $this->whereIn('educational_level',$levels);
    }

}