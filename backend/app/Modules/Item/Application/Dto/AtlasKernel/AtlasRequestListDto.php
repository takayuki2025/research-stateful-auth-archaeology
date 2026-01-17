<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Dto\AtlasKernel;

final class AtlasRequestListDto
{
    public function __construct(
        public readonly int $requestId,
        public readonly string $shopCode,
        public readonly array $item,

        public readonly ?string $submittedAt,
        public readonly string $analyzedAt,
        public readonly ?string $decidedAt,

        public readonly string $analysisVersion,
        public readonly string $requestStatus,

        public readonly array $before,
        public readonly array $ai,
        public readonly ?array $decision,
        public readonly ?array $diff,
        public readonly ?array $final,
        public readonly array $trigger,
        public readonly ?array $error,
    ) {}

    public function toArray(): array
    {
        return [
            'request_id'      => $this->requestId,
            'shop_code'       => $this->shopCode,
            'item'            => $this->item,
            'submitted_at'    => $this->submittedAt,
            'analyzed_at'     => $this->analyzedAt,
            'decided_at'      => $this->decidedAt,
            'analysis_version'=> $this->analysisVersion,
            'request_status'  => $this->requestStatus,
            'before'          => $this->before,
            'ai'              => $this->ai,
            'decision'        => $this->decision,
            'diff'            => $this->diff,
            'final'           => $this->final,
            'trigger'         => $this->trigger,
            'error'           => $this->error,
        ];
    }
}