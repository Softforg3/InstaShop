<?php

declare(strict_types=1);

namespace App\Tests\Unit\Gallery\Dto;

use App\Gallery\Dto\GalleryFilterDto;
use PHPUnit\Framework\TestCase;

class GalleryFilterDtoTest extends TestCase
{
    public function testEmptyDtoIsEmpty(): void
    {
        $dto = new GalleryFilterDto();

        $this->assertTrue($dto->isEmpty());
        $this->assertSame([], $dto->toArray());
    }

    public function testDtoWithValuesIsNotEmpty(): void
    {
        $dto = new GalleryFilterDto(location: 'Alps', camera: 'Canon');

        $this->assertFalse($dto->isEmpty());
    }

    public function testToArrayFiltersNullAndEmptyValues(): void
    {
        $dto = new GalleryFilterDto(
            location: 'Alps',
            camera: null,
            description: '',
            username: 'nature_lover',
        );

        $expected = [
            'location' => 'Alps',
            'username' => 'nature_lover',
        ];

        $this->assertSame($expected, $dto->toArray());
    }

    public function testToArrayMapsDateFieldsToSnakeCase(): void
    {
        $dto = new GalleryFilterDto(dateFrom: '2024-01-01', dateTo: '2024-12-31');

        $result = $dto->toArray();

        $this->assertArrayHasKey('date_from', $result);
        $this->assertArrayHasKey('date_to', $result);
    }
}
