<?php

declare(strict_types=1);

namespace App\CQRS\Query\GetProfile;

final class GetProfileQuery
{
    public function __construct(
        public readonly int $userId,
    ) {}
}
