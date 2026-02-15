<?php

declare(strict_types=1);

namespace App\Gallery\Filter;

use App\Gallery\Dto\FilterCriteria;
use App\Gallery\Dto\FilterCriteriaCollection;
use App\Gallery\Dto\FilterOperator;

final class UsernameFilter implements PhotoFilterInterface
{
    public function supports(string $field): bool
    {
        return $field === 'username';
    }

    public function buildCriteria(string $field, mixed $value): FilterCriteriaCollection
    {
        return (new FilterCriteriaCollection())
            ->add(new FilterCriteria('username', FilterOperator::Equals, $value));
    }
}
