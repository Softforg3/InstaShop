<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class PhotoNotFoundException extends NotFoundException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Photo with ID %d not found', $id));
    }
}
