<?php

namespace App\Modules\Payment\Domain\Port;

final class FeeAmount
{
    public function __construct(
        public readonly int $feeAmount,     // 正の金額
        public readonly string $currency,   // jpy
    ) {
    }
}

interface FeeQueryPort
{
    public function getFeeByBalanceTransactionId(string $balanceTransactionId): FeeAmount;
}