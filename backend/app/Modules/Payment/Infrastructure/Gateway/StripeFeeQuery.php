<?php

namespace App\Modules\Payment\Infrastructure\Gateway;

use App\Modules\Payment\Domain\Port\FeeQueryPort;
use App\Modules\Payment\Domain\Port\FeeAmount;
use Stripe\StripeClient;

final class StripeFeeQuery implements FeeQueryPort
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient([
            'api_key' => config('services.stripe.secret'),
            'stripe_version' => config('services.stripe.api_version'),
        ]);
    }

    public function getFeeByBalanceTransactionId(string $balanceTransactionId): FeeAmount
    {
        $bt = $this->stripe->balanceTransactions->retrieve($balanceTransactionId, []);

        // Stripe: fee は通貨の最小単位（JPYなら円）
        $fee = (int)($bt->fee ?? 0);
        $cur = strtoupper((string)($bt->currency ?? 'jpy'));

        return new FeeAmount($fee, $cur);
    }
}