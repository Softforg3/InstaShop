<?php

declare(strict_types=1);

namespace App\CQRS\Command\LikePhoto;

final class LikePhotoCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $photoId,
    ) {}
}
