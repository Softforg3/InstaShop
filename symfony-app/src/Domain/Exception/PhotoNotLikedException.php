<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class PhotoNotLikedException extends DomainException
{
    public static function create(int $photoId, int $userId): self
    {
        return new self(sprintf('Photo %d not liked by user %d', $photoId, $userId));
    }
}
