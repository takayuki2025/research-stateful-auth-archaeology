<?php

namespace App\Modules\Item\ReadModel\PublicCatalog;

use Countable;
use IteratorAggregate;
use ArrayIterator;

final class PublicCatalogItemCollection implements Countable, IteratorAggregate
{
    /** @var PublicCatalogItemDto[] */
    private array $items;

    /**
     * @param PublicCatalogItemDto[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return PublicCatalogItemDto[]
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function toArray(): array
    {
        return array_map(
            fn (PublicCatalogItemDto $dto) => $dto->toArray(),
            $this->items
        );
    }
}
