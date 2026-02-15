<?php

declare(strict_types=1);

namespace App\Gallery\Filter;

use App\Gallery\Dto\FilterCriteria;
use App\Gallery\Dto\FilterCriteriaCollection;
use App\Gallery\Dto\FilterOperator;

final class TextFilter implements PhotoFilterInterface
{
    private const SUPPORTED_FIELDS = ['location', 'camera', 'description'];

    public function supports(string $field): bool
    {
        return in_array($field, self::SUPPORTED_FIELDS, true);
    }

    public function buildCriteria(string $field, mixed $value): FilterCriteriaCollection
    {
        return (new FilterCriteriaCollection())
            ->add(new FilterCriteria($field, FilterOperator::Like, $value));
    }
}
