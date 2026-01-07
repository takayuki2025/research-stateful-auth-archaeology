<?php

namespace App\Modules\Search\Domain\Collection;

use Countable;
use IteratorAggregate;
use ArrayIterator;

final class SearchResultItems implements Countable, IteratorAggregate
{
    public function __construct(
        private array $items,
        private int $total
    ) {
    }

    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
