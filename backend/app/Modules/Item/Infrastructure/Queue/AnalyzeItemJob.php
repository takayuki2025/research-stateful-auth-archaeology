<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Queue;

use App\Modules\Item\Domain\Repository\AnalysisRequestRepository;
use App\Modules\AtlasKernel\Application\Service\AtlasKernelAnalyzer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class AnalyzeItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $analysisRequestId,
    ) {}

    public function handle(
        AnalysisRequestRepository $requests,
        AtlasKernelAnalyzer $analyzer,
    ): void {
        // ① running にできなければ終了（多重実行防止）
        if (! $requests->markRunning($this->analysisRequestId)) {
            return;
        }

        try {
            // ② 実解析（中身は Analyzer に委譲）
            $analyzer->analyze($this->analysisRequestId);

            // ③ 成功
            $requests->markDone($this->analysisRequestId);
        } catch (Throwable $e) {
            // ④ 失敗（retry は Queue に任せる）
            $requests->markFailed(
                $this->analysisRequestId,
                errorCode: class_basename($e),
                errorMessage: $e->getMessage(),
            );

            throw $e; // retry / backoff 用
        }
    }
}