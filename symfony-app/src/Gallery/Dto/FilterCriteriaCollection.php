<?php

declare(strict_types=1);

namespace App\Gallery\Dto;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, FilterCriteria>
 */
final class FilterCriteriaCollection implements IteratorAggregate, Countable
{
    /** @var FilterCriteria[] */
    private array $items = [];

    public function add(FilterCriteria $criteria): self
    {
        $this->items[] = $criteria;

        return $this;
    }

    public function merge(self $other): self
    {
        foreach ($other as $criteria) {
            $this->items[] = $criteria;
        }

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
