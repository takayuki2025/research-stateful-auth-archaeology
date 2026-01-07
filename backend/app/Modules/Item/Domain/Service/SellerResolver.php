<?php

namespace App\Modules\Item\Domain\Service;

use App\Modules\Item\Domain\ValueObject\SellerId;
use App\Modules\Item\Domain\ValueObject\SellerType;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

final class SellerResolver
{
    public function resolve(
        string $rawSellerId,
        AuthPrincipal $principal,
        ?int $tenantId = null,
    ): SellerId {

        [$type, $id] = $this->parse($rawSellerId);

        return match ($type) {
            SellerType::INDIVIDUAL =>
                $this->resolveIndividual($id, $principal),

            SellerType::SHOP =>
                $this->resolveShop($id, $principal, $tenantId),
        };
    }

    private function parse(string $raw): array
    {
        if (! str_contains($raw, ':')) {
            throw new \InvalidArgumentException('Invalid seller_id format.');
        }

        [$type, $id] = explode(':', $raw, 2);

        return [
            SellerType::from($type),
            (int) $id
        ];
    }

    /**
     * individual:2 でも shop を必ず返す（personal shop）
     * - ここで personal shop を作らない方針なら "findOrFail" で落としてOK
     * - ただし今回の仕様は「一般ユーザーも shop を持つ」なので、作成してしまうのが自然
     */
    private function resolveIndividual(
        int $userId,
        AuthPrincipal $principal,
    ): SellerId {
        if ($principal->userId !== $userId) {
            throw new \DomainException('Cannot sell as another user.');
        }

        $personalShopId = $this->resolveOrCreatePersonalShopId($userId);

        // ✅ 出品主体は personal shop
        return SellerId::shop($personalShopId);
    }

    private function resolveShop(
        int $shopId,
        AuthPrincipal $principal,
        ?int $tenantId,
    ): SellerId {
        if ($tenantId !== null && $tenantId !== $shopId) {
            throw new \DomainException('Tenant mismatch.');
        }

        $hasRole = DB::table('role_user')
            ->where('user_id', $principal->userId)
            ->where('shop_id', $shopId)
            ->exists();

        if (! $hasRole) {
            throw new \DomainException('No permission for this shop.');
        }

        return SellerId::shop($shopId);
    }

    private function resolveOrCreatePersonalShopId(int $userId): int
    {
        $shop = Shop::query()
            ->where('owner_user_id', $userId)
            ->where('type', 'personal')
            ->first();

        if ($shop) {
            return (int) $shop->id;
        }

        // ✅ personal shop を自動生成（運用ポリシーに合わせて調整可）
        $shop = Shop::create([
            'name' => "Personal Shop {$userId}",
            'shop_code' => 'personal-' . $userId,
            'owner_user_id' => $userId,
            'type' => 'personal',
            'status' => 'active',
        ]);

        return (int) $shop->id;
    }
}
