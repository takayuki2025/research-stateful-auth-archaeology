<?php

namespace App\Modules\Item\Domain\ValueObject;

enum ItemStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case SUSPENDED = 'suspended';
}