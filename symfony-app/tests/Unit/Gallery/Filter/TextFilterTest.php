<?php

declare(strict_types=1);

namespace App\Tests\Unit\Gallery\Filter;

use App\Gallery\Dto\FilterOperator;
use App\Gallery\Filter\TextFilter;
use PHPUnit\Framework\TestCase;

class TextFilterTest extends TestCase
{
    private TextFilter $filter;

    protected function setUp(): void
    {
        $this->filter = new TextFilter();
    }

    public function testSupportsLocationField(): void
    {
        $this->assertTrue($this->filter->supports('location'));
    }

    public function testSupportsCameraField(): void
    {
        $this->assertTrue($this->filter->supports('camera'));
    }

    public function testSupportsDescriptionField(): void
    {
        $this->assertTrue($this->filter->supports('description'));
    }

    public function testDoesNotSupportUnknownField(): void
    {
        $this->assertFalse($this->filter->supports('username'));
        $this->assertFalse($this->filter->supports('date_from'));
    }

    public function testBuildCriteriaReturnsLikeOperator(): void
    {
        $criteria = $this->filter->buildCriteria('location', 'Alps');

        $this->assertCount(1, $criteria);

        $items = iterator_to_array($criteria);
        $this->assertSame('location', $items[0]->field);
        $this->assertSame(FilterOperator::Like, $items[0]->operator);
        $this->assertSame('Alps', $items[0]->value);
    }
}
