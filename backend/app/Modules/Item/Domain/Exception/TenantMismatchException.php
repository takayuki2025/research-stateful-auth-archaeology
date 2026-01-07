<?php

namespace App\Modules\Item\Domain\Exception;

use RuntimeException;

final class TenantMismatchException extends RuntimeException
{
    public function __construct(?int $tenantId, ?int $shopId)
    {
        parent::__construct("Tenant mismatch: tenant_id={$tenantId}, shop_id={$shopId}");
    }
}
