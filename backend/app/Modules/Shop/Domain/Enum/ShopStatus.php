<?php

namespace App\Modules\Shop\Domain\Enum;

enum ShopStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
}
