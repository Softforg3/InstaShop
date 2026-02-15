<?php

declare(strict_types=1);

namespace App\Gallery\Filter;

use App\Gallery\Dto\FilterCriteria;
use App\Gallery\Dto\FilterCriteriaCollection;
use App\Gallery\Dto\FilterOperator;
use DateTimeImmutable;

final class DateRangeFilter implements PhotoFilterInterface
{
    public function supports(string $field): bool
    {
        return in_array($field, ['date_from', 'date_to'], true);
    }

    public function buildCriteria(string $field, mixed $value): FilterCriteriaCollection
    {
        $collection = new FilterCriteriaCollection();

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        if (!$date) {
            return $collection;
        }

        if ($field === 'date_from') {
            $collection->add(new FilterCriteria('takenAt', FilterOperator::GreaterOrEqual, $date->setTime(0, 0)));
        } else {
            $collection->add(new FilterCriteria('takenAt', FilterOperator::LessOrEqual, $date->setTime(23, 59, 59)));
        }

        return $collection;
    }
}
