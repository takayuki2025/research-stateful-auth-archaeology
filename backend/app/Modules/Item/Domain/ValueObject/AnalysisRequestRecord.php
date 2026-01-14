<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\ValueObject;

final readonly class AnalysisRequestRecord
{
    public function __construct(
        private int $id,
        private ?int $tenantId,
        private int $itemId,

        /**
         * v3固定：
         * - Draft 起点でない request もある
         * - replay / system / legacy を考慮
         */
        private ?string $itemDraftId,

        private string $analysisVersion,
        private string $rawText,
        private string $status,
    ) {}

    /* =========================
       Identity
    ========================= */

    public function id(): int
    {
        return $this->id;
    }

    public function tenantId(): ?int
    {
        return $this->tenantId;
    }

    public function itemId(): int
    {
        return $this->itemId;
    }

    /* =========================
       Draft (optional)
    ========================= */

    public function itemDraftId(): ?string
    {
        return $this->itemDraftId;
    }

    public function hasDraft(): bool
    {
        return $this->itemDraftId !== null;
    }

    /* =========================
       Analysis
    ========================= */

    public function analysisVersion(): string
    {
        return $this->analysisVersion;
    }

    /**
     * SoT：解析入力（item_draft snapshot）
     */
    public function rawText(): string
    {
        return $this->rawText;
    }

    /* =========================
       Status
    ========================= */

    public function status(): string
    {
        return $this->status;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}