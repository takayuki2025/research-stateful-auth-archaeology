<?php

namespace App\Modules\Payment\Domain\Entity;

use App\Modules\Payment\Domain\Enum\PaymentMethod;
use App\Modules\Payment\Domain\Enum\PaymentProvider;
use App\Modules\Payment\Domain\Enum\PaymentStatus;

final class Payment
{
    private function __construct(
        private ?int $id,
        private int $orderId,
        private int $shopId,
        private int $userId,
        private PaymentProvider $provider,
        private PaymentMethod $method,
        private PaymentStatus $status,
        private int $amount,
        private string $currency,
        private ?string $providerPaymentId,
        private ?string $providerCustomerId,
        private ?array $methodDetails,
        private ?array $instructions,
        private ?array $meta,
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('amount must be >= 0');
        }
        if ($currency === '') {
            throw new \InvalidArgumentException('currency is required');
        }
    }

    /* ============================================
       Factory
    ============================================ */

    public static function initiate(
        int $orderId,
        int $shopId,
        int $userId,
        PaymentProvider $provider,
        PaymentMethod $method,
        int $amount,
        string $currency,
        ?array $meta = null
    ): self {
        return new self(
            id: null,
            orderId: $orderId,
            shopId: $shopId,
            userId: $userId,
            provider: $provider,
            method: $method,
            status: PaymentStatus::INITIATED,
            amount: $amount,
            currency: $currency,
            providerPaymentId: null,
            providerCustomerId: null,
            methodDetails: null,
            instructions: null,
            meta: $meta
        );
    }

    public static function reconstitute(
        ?int $id,
        int $orderId,
        int $shopId,
        int $userId,
        PaymentProvider $provider,
        PaymentMethod $method,
        PaymentStatus $status,
        int $amount,
        string $currency,
        ?string $providerPaymentId,
        ?string $providerCustomerId,
        ?array $methodDetails,
        ?array $instructions,
        ?array $meta,
    ): self {
        return new self(
            id: $id,
            orderId: $orderId,
            shopId: $shopId,
            userId: $userId,
            provider: $provider,
            method: $method,
            status: $status,
            amount: $amount,
            currency: $currency,
            providerPaymentId: $providerPaymentId,
            providerCustomerId: $providerCustomerId,
            methodDetails: $methodDetails,
            instructions: $instructions,
            meta: $meta
        );
    }

    /* ============================================
       Getters
    ============================================ */

    public function id(): ?int
    {
        return $this->id;
    }
    public function orderId(): int
    {
        return $this->orderId;
    }
    public function shopId(): int
    {
        return $this->shopId;
    }
    public function userId(): int
    {
        return $this->userId;
    }
    public function provider(): PaymentProvider
    {
        return $this->provider;
    }
    public function method(): PaymentMethod
    {
        return $this->method;
    }
    public function status(): PaymentStatus
    {
        return $this->status;
    }
    public function amount(): int
    {
        return $this->amount;
    }
    public function currency(): string
    {
        return $this->currency;
    }
    public function providerPaymentId(): ?string
    {
        return $this->providerPaymentId;
    }
    public function providerCustomerId(): ?string
    {
        return $this->providerCustomerId;
    }
    public function methodDetails(): ?array
    {
        return $this->methodDetails;
    }
    public function instructions(): ?array
    {
        return $this->instructions;
    }
    public function meta(): ?array
    {
        return $this->meta;
    }

    /* ============================================
       Mutations（immutable）
    ============================================ */

    public function withProviderPayment(string $providerPaymentId, ?string $customerId = null): self
    {
        return self::reconstitute(
            id: $this->id,
            orderId: $this->orderId,
            shopId: $this->shopId,
            userId: $this->userId,
            provider: $this->provider,
            method: $this->method,
            status: $this->status,
            amount: $this->amount,
            currency: $this->currency,
            providerPaymentId: $providerPaymentId,
            providerCustomerId: $customerId,
            methodDetails: $this->methodDetails,
            instructions: $this->instructions,
            meta: $this->meta
        );
    }

    public function withMethodDetails(array $methodDetails): self
    {
        return self::reconstitute(
            id: $this->id,
            orderId: $this->orderId,
            shopId: $this->shopId,
            userId: $this->userId,
            provider: $this->provider,
            method: $this->method,
            status: $this->status,
            amount: $this->amount,
            currency: $this->currency,
            providerPaymentId: $this->providerPaymentId,
            providerCustomerId: $this->providerCustomerId,
            methodDetails: $methodDetails,
            instructions: $this->instructions,
            meta: $this->meta
        );
    }

    public function withInstructions(array $instructions): self
    {
        return self::reconstitute(
            id: $this->id,
            orderId: $this->orderId,
            shopId: $this->shopId,
            userId: $this->userId,
            provider: $this->provider,
            method: $this->method,
            status: $this->status,
            amount: $this->amount,
            currency: $this->currency,
            providerPaymentId: $this->providerPaymentId,
            providerCustomerId: $this->providerCustomerId,
            methodDetails: $this->methodDetails,
            instructions: $instructions,
            meta: $this->meta
        );
    }

    public function markRequiresAction(?array $meta = null): self
    {
        return self::reconstitute(
            id: $this->id,
            orderId: $this->orderId,
            shopId: $this->shopId,
            userId: $this->userId,
            provider: $this->provider,
            method: $this->method,
            status: PaymentStatus::REQUIRES_ACTION,
            amount: $this->amount,
            currency: $this->currency,
            providerPaymentId: $this->providerPaymentId,
            providerCustomerId: $this->providerCustomerId,
            methodDetails: $this->methodDetails,
            instructions: $this->instructions,
            meta: $meta ?? $this->meta
        );
    }

    public function markSucceeded(?array $meta = null): self
    {
        return self::reconstitute(
            id: $this->id,
            orderId: $this->orderId,
            shopId: $this->shopId,
            userId: $this->userId,
            provider: $this->provider,
            method: $this->method,
            status: PaymentStatus::SUCCEEDED,
            amount: $this->amount,
            currency: $this->currency,
            providerPaymentId: $this->providerPaymentId,
            providerCustomerId: $this->providerCustomerId,
            methodDetails: $this->methodDetails,
            instructions: $this->instructions,
            meta: $meta ?? $this->meta
        );
    }

    public function markFailed(?array $meta = null): self
    {
        return self::reconstitute(
            id: $this->id,
            orderId: $this->orderId,
            shopId: $this->shopId,
            userId: $this->userId,
            provider: $this->provider,
            method: $this->method,
            status: PaymentStatus::FAILED,
            amount: $this->amount,
            currency: $this->currency,
            providerPaymentId: $this->providerPaymentId,
            providerCustomerId: $this->providerCustomerId,
            methodDetails: $this->methodDetails,
            instructions: $this->instructions,
            meta: $meta ?? $this->meta
        );
    }
}
