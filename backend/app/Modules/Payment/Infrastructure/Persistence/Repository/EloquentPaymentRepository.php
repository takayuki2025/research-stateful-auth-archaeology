<?php

namespace App\Modules\Payment\Infrastructure\Persistence\Repository;

use App\Modules\Payment\Domain\Entity\Payment;
use App\Modules\Payment\Domain\Enum\PaymentMethod;
use App\Modules\Payment\Domain\Enum\PaymentProvider;
use App\Modules\Payment\Domain\Enum\PaymentStatus;
use App\Modules\Payment\Domain\Repository\PaymentRepository;
use Illuminate\Support\Facades\DB;

final class EloquentPaymentRepository implements PaymentRepository
{
    public function save(Payment $payment): Payment
    {
        /**
         * ==================================================
         * INSERT
         * ==================================================
         */
        if ($payment->id() === null) {

            $id = DB::table('payments')->insertGetId([
                'order_id' => $payment->orderId(),
                'shop_id' => $payment->shopId(),
                'user_id' => $payment->userId(),
                'provider' => $payment->provider()->value,
                'method' => $payment->method()->value,
                'status' => $payment->status()->value,
                'amount' => $payment->amount(),
                'currency' => $payment->currency(),
                'provider_payment_id' => $payment->providerPaymentId(),
                'provider_customer_id' => $payment->providerCustomerId(),
                'method_details' => $payment->methodDetails()
                    ? json_encode($payment->methodDetails(), JSON_UNESCAPED_UNICODE)
                    : null,
                'instructions' => $payment->instructions()
                    ? json_encode($payment->instructions(), JSON_UNESCAPED_UNICODE)
                    : null,
                'meta' => $payment->meta()
                    ? json_encode($payment->meta(), JSON_UNESCAPED_UNICODE)
                    : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return Payment::reconstitute(
                id: (int)$id,
                orderId: $payment->orderId(),
                shopId: $payment->shopId(),
                userId: $payment->userId(),
                provider: $payment->provider(),
                method: $payment->method(),
                status: $payment->status(),
                amount: $payment->amount(),
                currency: $payment->currency(),
                providerPaymentId: $payment->providerPaymentId(),
                providerCustomerId: $payment->providerCustomerId(),
                methodDetails: $payment->methodDetails(),
                instructions: $payment->instructions(),
                meta: $payment->meta(),
            );
        }

        /**
         * ==================================================
         * UPDATE（★ ここが最重要修正点）
         * ==================================================
         */
        $update = [
            'status' => $payment->status()->value,
            'provider_payment_id' => $payment->providerPaymentId(),
            'provider_customer_id' => $payment->providerCustomerId(),
            'updated_at' => now(),
        ];

        if ($payment->methodDetails() !== null) {
            $update['method_details'] =
                json_encode($payment->methodDetails(), JSON_UNESCAPED_UNICODE);
        }

        if ($payment->instructions() !== null) {
            $update['instructions'] =
                json_encode($payment->instructions(), JSON_UNESCAPED_UNICODE);
        }

        if ($payment->meta() !== null) {
            $update['meta'] =
                json_encode($payment->meta(), JSON_UNESCAPED_UNICODE);
        }

        DB::table('payments')
            ->where('id', $payment->id())
            ->update($update);

        return $payment;
    }

    public function findById(int $paymentId): ?Payment
    {
        $row = DB::table('payments')->where('id', $paymentId)->first();
        return $row ? $this->mapRow($row) : null;
    }

    public function findByProviderPaymentId(string $providerPaymentId): ?Payment
    {
        $row = DB::table('payments')
            ->where('provider_payment_id', $providerPaymentId)
            ->first();

        return $row ? $this->mapRow($row) : null;
    }

    public function updateStatusById(int $paymentId, string $status, ?array $meta = null): void
    {
        $update = [
            'status' => $status,
            'updated_at' => now(),
        ];

        if ($meta !== null) {
            $update['meta'] = json_encode($meta, JSON_UNESCAPED_UNICODE);
        }

        DB::table('payments')
            ->where('id', $paymentId)
            ->update($update);
    }

    private function mapRow(object $row): Payment
    {
        return Payment::reconstitute(
            id: (int)$row->id,
            orderId: (int)$row->order_id,
            shopId: (int)$row->shop_id,
            userId: (int)$row->user_id,
            provider: PaymentProvider::from((string)$row->provider),
            method: PaymentMethod::from((string)$row->method),
            status: PaymentStatus::from((string)$row->status),
            amount: (int)$row->amount,
            currency: (string)$row->currency,
            providerPaymentId: $row->provider_payment_id ?: null,
            providerCustomerId: $row->provider_customer_id ?: null,
            methodDetails: $row->method_details
                ? json_decode($row->method_details, true)
                : null,
            instructions: $row->instructions
                ? json_decode($row->instructions, true)
                : null,
            meta: $row->meta
                ? json_decode($row->meta, true)
                : null,
        );
    }

    public function findLatestByOrderId(int $orderId): ?Payment
    {
        $row = DB::table('payments')
            ->where('order_id', $orderId)
            ->orderByDesc('id')
            ->first();

        return $row ? $this->mapRow($row) : null;
    }

    public function findByProviderPaymentIdAndOrderId(
        string $providerPaymentId,
        int $orderId
    ): ?Payment {
        $row = DB::table('payments')
            ->where('provider_payment_id', $providerPaymentId)
            ->where('order_id', $orderId)
            ->first();

        return $row ? $this->mapRow($row) : null;
    }
}
