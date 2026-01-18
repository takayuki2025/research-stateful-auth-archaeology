<?php

namespace App\Modules\Payment\Domain\Entity\Wallet;

final class StoredPaymentMethod
{
    private function __construct(
        private int $id,
        private int $walletId,
        private string $provider,
        private string $providerPaymentMethodId,
        private ?string $brand,
        private ?string $last4,
        private ?int $expMonth,
        private ?int $expYear,
        private bool $isDefault,
        private string $status,
        private ?array $meta,
    ) {
    }

    public static function reconstitute(
        int $id,
        int $walletId,
        string $provider,
        string $providerPaymentMethodId,
        ?string $brand,
        ?string $last4,
        ?int $expMonth,
        ?int $expYear,
        bool $isDefault,
        string $status,
        ?array $meta,
    ): self {
        return new self(
            $id, $walletId, $provider, $providerPaymentMethodId,
            $brand, $last4, $expMonth, $expYear,
            $isDefault, $status, $meta
        );
    }

    public function id(): int { return $this->id; }
    public function walletId(): int { return $this->walletId; }
    public function provider(): string { return $this->provider; }
    public function providerPaymentMethodId(): string { return $this->providerPaymentMethodId; }
    public function brand(): ?string { return $this->brand; }
    public function last4(): ?string { return $this->last4; }
    public function expMonth(): ?int { return $this->expMonth; }
    public function expYear(): ?int { return $this->expYear; }
    public function isDefault(): bool { return $this->isDefault; }
    public function status(): string { return $this->status; }
    public function meta(): ?array { return $this->meta; }
}