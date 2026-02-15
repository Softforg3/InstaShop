<?php

declare(strict_types=1);

namespace App\Gallery\Dto;

final class GalleryFilterDto
{
    public function __construct(
        public readonly ?string $location = null,
        public readonly ?string $camera = null,
        public readonly ?string $description = null,
        public readonly ?string $username = null,
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
    ) {}

    public function isEmpty(): bool
    {
        return $this->toArray() === [];
    }

    /**
     * @return array<string, string> Only non-empty filter values
     */
    public function toArray(): array
    {
        $map = [
            'location' => $this->location,
            'camera' => $this->camera,
            'description' => $this->description,
            'username' => $this->username,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ];

        return array_filter($map, fn($v) => $v !== null && $v !== '');
    }
}
