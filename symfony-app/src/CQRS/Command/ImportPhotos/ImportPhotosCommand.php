<?php

declare(strict_types=1);

namespace App\CQRS\Command\ImportPhotos;

final class ImportPhotosCommand
{
    public function __construct(
        public readonly int $userId,
    ) {}
}
