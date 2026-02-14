<?php

declare(strict_types=1);

namespace App\Domain\Dto;

final class PhoenixPhotoDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $photoUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            (string) $data['photo_url'],
        );
    }
}
