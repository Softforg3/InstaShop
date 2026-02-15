<?php

declare(strict_types=1);

namespace App\Gallery\Dto;

enum FilterOperator: string
{
    case Like = 'like';
    case Equals = 'eq';
    case GreaterOrEqual = 'gte';
    case LessOrEqual = 'lte';
}
