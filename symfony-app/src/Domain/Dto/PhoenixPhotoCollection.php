<?php

declare(strict_types=1);

namespace App\Domain\Dto;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, PhoenixPhotoDto>
 */
final class PhoenixPhotoCollection implements IteratorAggregate, Countable
{
    /** @var PhoenixPhotoDto[] */
    private readonly array $items;

    public function __construct(PhoenixPhotoDto ...$items)
    {
        $this->items = $items;
    }

    public static function fromArray(array $data): self
    {
        return new self(...array_map(
            fn(array $item) => PhoenixPhotoDto::fromArray($item),
            $data
        ));
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
