<?php

declare(strict_types=1);

namespace App\CQRS\Command\UnlikePhoto;

final class UnlikePhotoCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $photoId,
    ) {}
}
