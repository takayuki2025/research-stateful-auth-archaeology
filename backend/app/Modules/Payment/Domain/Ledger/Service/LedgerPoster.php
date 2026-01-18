<?php

namespace App\Modules\Payment\Domain\Ledger\Service;

use App\Modules\Payment\Domain\Ledger\AccountCode;
use App\Modules\Payment\Domain\Ledger\PostingType;

final class LedgerPoster
{
    /**
     * @return array{entries: array<int, array>, debit_total:int, credit_total:int}
     */
    public function buildEntries(string $postingType, int $amount, string $currency): array
    {
        if ($amount <= 0) {
            throw new \DomainException('amount must be positive');
        }

        return match ($postingType) {
            PostingType::SALE => $this->sale($amount, $currency),
            PostingType::REFUND => $this->refund($amount, $currency),
            default => throw new \DomainException('Unknown posting_type: ' . $postingType),
        };
    }

    private function sale(int $amount, string $currency): array
    {
        // 借方：CASH_CLEARING / 貸方：SALES_REVENUE
        $entries = [
            ['account_code' => AccountCode::CASH_CLEARING, 'side' => 'debit', 'amount' => $amount, 'currency' => $currency],
            ['account_code' => AccountCode::SALES_REVENUE, 'side' => 'credit', 'amount' => $amount, 'currency' => $currency],
        ];

        return ['entries' => $entries, 'debit_total' => $amount, 'credit_total' => $amount];
    }

    private function refund(int $amount, string $currency): array
    {
        // 借方：REFUND_EXPENSE / 貸方：CASH_CLEARING
        $entries = [
            ['account_code' => AccountCode::REFUND_EXPENSE, 'side' => 'debit', 'amount' => $amount, 'currency' => $currency],
            ['account_code' => AccountCode::CASH_CLEARING, 'side' => 'credit', 'amount' => $amount, 'currency' => $currency],
        ];

        return ['entries' => $entries, 'debit_total' => $amount, 'credit_total' => $amount];
    }
}