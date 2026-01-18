<?php

namespace App\Modules\Payment\Domain\Ledger;

final class AccountCode
{
    // Stripe等PSPの入金・決済クリアリング（現金相当の受取口座）
    public const CASH_CLEARING = 'CASH_CLEARING';

    // 売上
    public const SALES_REVENUE = 'SALES_REVENUE';

    // 返金（費用） ※ v2-1の簡易扱い
    public const REFUND_EXPENSE = 'REFUND_EXPENSE';

    // v2-3（Stripe手数料）
    public const FEE_EXPENSE = 'FEE_EXPENSE';
}