<?php

namespace App\Modules\Payment\Domain\Repository;

use App\Modules\Payment\Domain\Entity\Payment;

interface PaymentRepository
{
    public function save(Payment $payment): Payment;

    public function findById(int $paymentId): ?Payment;

    public function findByProviderPaymentId(string $providerPaymentId): ?Payment;

    public function updateStatusById(int $paymentId, string $status, ?array $meta = null): void;

    public function findLatestByOrderId(int $orderId): ?Payment;

    public function findByProviderPaymentIdAndOrderId(
        string $providerPaymentId,
        int $orderId
    ): ?Payment;
}
