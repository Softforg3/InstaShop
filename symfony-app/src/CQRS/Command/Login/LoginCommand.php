<?php

declare(strict_types=1);

namespace App\CQRS\Command\Login;

final class LoginCommand
{
    public function __construct(
        public readonly string $username,
        public readonly string $token,
    ) {}
}
