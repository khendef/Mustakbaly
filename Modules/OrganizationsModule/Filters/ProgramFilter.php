<?php
namespace Modules\OrganizationsModule\Filters;
use Illuminate\Database\Eloquent\Builder;
use Modules\OrganizationsModule\Models\Program;
use Modules\OrganizationsModule\Filters\BaseFilter;

class ProgramFilter
{
    public function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn ($q, $status) =>
                $q->where('status', $status)
            )
            ->when($filters['organization_id'] ?? null, fn ($q, $org) =>
                $q->where('organization_id', $org)
            )
            ->when($filters['min_budget'] ?? null, fn ($q, $min) =>
                $q->where('required_budget', '>=', $min)
            )
            ->when($filters['max_budget'] ?? null, fn ($q, $max) =>
                $q->where('required_budget', '<=', $max)
            )
            ->when($filters['funded'] ?? null, function ($q, $funded) {
                if ($funded === 'true') {
                    $q->whereColumn(
                        'total_funded_amount',
                        '>=',
                        'required_budget'
                    );
                }
            });
    }
}
