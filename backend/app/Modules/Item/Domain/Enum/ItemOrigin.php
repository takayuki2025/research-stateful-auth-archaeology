<?php

namespace App\Modules\Item\Domain\Enum;

enum ItemOrigin: string
{
    case SHOP_MANAGED = 'shop_managed';
    case USER_PERSONAL = 'user_personal';
}
