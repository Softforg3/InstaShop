<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class UserNotFoundException extends NotFoundException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('User with ID %d not found', $id));
    }

    public static function withUsername(string $username): self
    {
        return new self(sprintf('User "%s" not found', $username));
    }
}
