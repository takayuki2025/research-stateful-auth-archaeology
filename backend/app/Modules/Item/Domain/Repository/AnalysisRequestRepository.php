<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Repository;

use App\Modules\Item\Domain\ValueObject\AnalysisRequestRecord;

interface AnalysisRequestRepository
{
    /**
     * 新規解析リクエスト作成（初回・Replay 共通）
     */
    public function create(array $attributes): int;

    /**
     * requestId 主語で取得（存在しなければ例外）
     */
    public function findOrFail(int $requestId): AnalysisRequestRecord;

    /**
     * 解析完了マーク
     */
    public function markDone(int $requestId): void;

    /**
     * 解析失敗マーク（将来拡張用）
     */
    public function markFailed(
        int $requestId,
        ?string $errorCode = null,
        ?string $errorMessage = null,
    ): void;
}