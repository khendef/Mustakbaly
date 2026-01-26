<?php
namespace Modules\CertificationModule\Filters;
use Illuminate\Database\Eloquent\Builder;

class Certificate
{
        public function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['organization_id'] ?? null,
                fn (Builder $q, int $orgId) =>
                    $q->where('organization_id', $orgId)
            )

            ->when($filters['from_date'] ?? null,
                fn (Builder $q, string $from) =>
                    $q->whereDate('issue_date', '>=', $from)
            )

            ->when($filters['to_date'] ?? null,
                fn (Builder $q, string $to) =>
                    $q->whereDate('issue_date', '<=', $to)
            )

            ->when($filters['certificate_number'] ?? null,
                fn (Builder $q, string $number) =>
                    $q->where('certificate_number', 'like', "%{$number}%")
            );
    }
}
