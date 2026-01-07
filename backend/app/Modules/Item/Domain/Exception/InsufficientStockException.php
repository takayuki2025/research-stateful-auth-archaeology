<?php

namespace App\Modules\Item\Domain\Exception;

use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public function __construct(int $itemId, int $requested, int $available)
    {
        parent::__construct("Insufficient stock: item_id={$itemId}, requested={$requested}, available={$available}");
    }
}