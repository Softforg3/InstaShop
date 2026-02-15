<?php

declare(strict_types=1);

namespace App\Gallery\Factory;

use App\Gallery\Dto\GalleryFilterDto;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;

final class GalleryFilterFactory
{
    /** @var array<string, string> query param name => constructor param name */
    private readonly array $paramMap;

    public function __construct()
    {
        $this->paramMap = $this->buildParamMap();
    }

    public function fromRequest(Request $request): GalleryFilterDto
    {
        $args = [];

        foreach ($this->paramMap as $queryParam => $constructorParam) {
            $value = $request->query->get($queryParam);
            $args[$constructorParam] = $this->normalize($value);
        }

        return new GalleryFilterDto(...$args);
    }

    private function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * Maps camelCase constructor params to snake_case query params.
     *
     * @return array<string, string>
     */
    private function buildParamMap(): array
    {
        $map = [];
        $reflection = new ReflectionClass(GalleryFilterDto::class);

        foreach ($reflection->getConstructor()->getParameters() as $param) {
            $name = $param->getName();
            $queryParam = strtolower(preg_replace('/[A-Z]/', '_$0', $name));
            $map[$queryParam] = $name;
        }

        return $map;
    }
}
