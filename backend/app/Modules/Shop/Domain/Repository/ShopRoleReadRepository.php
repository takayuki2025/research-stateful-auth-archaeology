<?php

namespace App\Modules\Shop\Domain\Repository;

interface ShopRoleReadRepository
{
    /**
     * @return array<int, array{
     *   shop_id:int,
     *   shop_code:string,
     *   role:string
     * }>
     */
    public function findByUserId(int $userId): array;
}
