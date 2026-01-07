<?php

namespace App\Modules\Item\Domain\ValueObject;

enum SellerType: string
{
    case INDIVIDUAL = 'individual';
    case SHOP       = 'shop';
}
