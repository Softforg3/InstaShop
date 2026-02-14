<?php

declare(strict_types=1);

namespace App\CQRS\Command\SavePhoenixToken;

final class SavePhoenixTokenCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $token,
    ) {}
}
