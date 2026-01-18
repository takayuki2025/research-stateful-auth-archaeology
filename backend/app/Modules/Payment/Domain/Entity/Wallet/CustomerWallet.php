<?php

namespace App\Modules\Payment\Domain\Entity\Wallet;

final class CustomerWallet
{
    private function __construct(
        private ?int $id,
        private int $userId,
        private ?int $shopId,
        private string $provider,
        private ?string $providerCustomerId,
        private string $status,
        private ?array $meta,
    ) {
    }

    public static function createForUser(
        int $userId,
        ?int $shopId = null,
        string $provider = 'stripe',
    ): self {
        return new self(
            id: null,
            userId: $userId,
            shopId: $shopId,
            provider: $provider,
            providerCustomerId: null,
            status: 'active',
            meta: null,
        );
    }

    public static function reconstitute(
        int $id,
        int $userId,
        ?int $shopId,
        string $provider,
        ?string $providerCustomerId,
        string $status,
        ?array $meta,
    ): self {
        return new self($id, $userId, $shopId, $provider, $providerCustomerId, $status, $meta);
    }

    public function id(): ?int { return $this->id; }
    public function userId(): int { return $this->userId; }
    public function shopId(): ?int { return $this->shopId; }
    public function provider(): string { return $this->provider; }
    public function providerCustomerId(): ?string { return $this->providerCustomerId; }
    public function status(): string { return $this->status; }
    public function meta(): ?array { return $this->meta; }
}