<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class PhoenixApiException extends DomainException
{
    public static function unauthorized(): self
    {
        return new self('Invalid Phoenix API token');
    }

    public static function connectionError(string $message): self
    {
        return new self('Phoenix API connection error: ' . $message);
    }
}
