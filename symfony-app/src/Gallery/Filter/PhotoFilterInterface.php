<?php

declare(strict_types=1);

namespace App\Gallery\Filter;

use App\Gallery\Dto\FilterCriteriaCollection;

interface PhotoFilterInterface
{
    public function supports(string $field): bool;

    public function buildCriteria(string $field, mixed $value): FilterCriteriaCollection;
}
