<?php

namespace App\Modules\Payment\Domain\Ledger\Port;

interface PostLedgerPort
{
    /**
     * Ledger投入（冪等）
     * - 既に処理済みなら何もしない（成功扱い）
     */
    public function post(PostLedgerCommand $cmd): void;
}