<?php

declare(strict_types=1);

namespace App\Gallery\Filter;

use App\Gallery\Dto\FilterCriteriaCollection;
use App\Gallery\Dto\GalleryFilterDto;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Traversable;

final class PhotoFilterRegistry
{
    /** @var PhotoFilterInterface[] */
    private readonly array $filters;

    public function __construct(
        #[TaggedIterator('app.photo_filter')] iterable $filters,
    ) {
        $this->filters = $filters instanceof Traversable
            ? iterator_to_array($filters)
            : (array) $filters;
    }

    public function resolve(GalleryFilterDto $dto): FilterCriteriaCollection
    {
        $collection = new FilterCriteriaCollection();

        foreach ($dto->toArray() as $field => $value) {
            foreach ($this->filters as $filter) {
                if ($filter->supports($field)) {
                    $collection->merge($filter->buildCriteria($field, $value));
                    break;
                }
            }
        }

        return $collection;
    }
}
