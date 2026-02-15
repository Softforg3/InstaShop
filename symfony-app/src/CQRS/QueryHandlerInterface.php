<?php

declare(strict_types=1);

namespace App\CQRS;

interface QueryHandlerInterface
{
    public static function getHandledQuery(): string;
}
