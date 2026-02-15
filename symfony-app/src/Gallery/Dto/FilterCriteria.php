<?php

declare(strict_types=1);

namespace App\Gallery\Dto;

final class FilterCriteria
{
    public function __construct(
        public readonly string $field,
        public readonly FilterOperator $operator,
        public readonly mixed $value,
    ) {}
}
