<?php

declare(strict_types=1);

namespace App\CQRS\Query\GetGallery;

final class GetGalleryQuery
{
    public function __construct(
        public readonly ?int $userId = null,
    ) {}
}
