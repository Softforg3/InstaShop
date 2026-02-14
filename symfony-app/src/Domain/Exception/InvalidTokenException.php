<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidTokenException extends DomainException
{
    public static function notFound(): self
    {
        return new self('Invalid token');
    }

    public static function mismatch(): self
    {
        return new self('Token does not belong to this user');
    }
}
