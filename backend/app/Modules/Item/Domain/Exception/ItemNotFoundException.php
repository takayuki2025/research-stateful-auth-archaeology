<?php

namespace App\Modules\Item\Domain\Exception;

use RuntimeException;

final class ItemNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Item not found');
    }
}
