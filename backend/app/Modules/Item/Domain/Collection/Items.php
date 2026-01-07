<?php

namespace App\Modules\Item\Domain\Collection;

use App\Modules\Item\Domain\Entity\Item;
use ArrayIterator;
use Countable;
use IteratorAggregate;

final class Items implements IteratorAggregate, Countable
{
    /** @var Item[] */
    private array $items;

    /**
     * @param Item[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Repository / UseCase 用
     */
    public static function fromArray(array $items): self
    {
        return new self($items);
    }

    /**
     * Repository から Eloquent Collection を受ける場合
     * ※ Repository 側で toDomain 済みが前提
     */
    public static function fromEloquent($models): self
    {
        return new self($models->all());
    }

    /**
     * Controller / Resource 用
     *
     * @return Item[]
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * foreach 対応
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * 個数
     */
    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
