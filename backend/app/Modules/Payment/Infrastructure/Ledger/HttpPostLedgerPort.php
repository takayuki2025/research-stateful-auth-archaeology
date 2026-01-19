<?php

namespace App\Modules\Payment\Infrastructure\Ledger;

use App\Modules\Payment\Domain\Ledger\Port\PostLedgerPort;
use App\Modules\Payment\Domain\Ledger\Port\PostLedgerCommand;
use Illuminate\Support\Facades\Http;

final class HttpPostLedgerPort implements PostLedgerPort
{
    public function post(PostLedgerCommand $cmd): void
    {
        $baseUrl = rtrim((string) config('trustledger.ledger.http.base_url'), '/');
        $timeout = (int) config('trustledger.ledger.http.timeout_seconds', 5);
        $apiKey  = config('trustledger.ledger.http.api_key');

        $req = Http::timeout($timeout)
            ->acceptJson()
            ->asJson();

        if (is_string($apiKey) && $apiKey !== '') {
            $req = $req->withHeaders(['X-TrustLedger-Key' => $apiKey]);
        }

        // ✅ Java/Kotlin側の契約：POST /ledger/post
        $res = $req->post($baseUrl . '/ledger/post', $cmd->toArray());

        if (! $res->ok()) {
            // 最小：エラーとして落とす（Webhook側では swallow される設計）
            $msg = $res->json('message') ?? $res->body();
            throw new \RuntimeException('PaymentCore ledger post failed: ' . $msg);
        }
    }
}