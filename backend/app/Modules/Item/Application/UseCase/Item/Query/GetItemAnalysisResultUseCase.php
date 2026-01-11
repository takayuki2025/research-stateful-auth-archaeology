<?php

namespace App\Modules\Item\Application\UseCase\Item\Query;

use App\Modules\Item\Infrastructure\Persistence\Query\AnalysisResultReadRepository;

final class GetItemAnalysisResultUseCase
{
    public function __construct(
        private readonly AnalysisResultReadRepository $repo
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function handle(int $itemId): array
    {
        $payload = $this->repo->findLatestActiveByItemId($itemId);

        if (! $payload) {
            return [
                'status' => 'not_analyzed',
            ];
        }

        return [
            'status'   => 'analyzed',
            'analysis' => $payload['analysis'] ?? [],
            'decision' => $payload['decision'] ?? null,
            'lineage'  => $payload['analysis']['lineage'] ?? null,
        ];
    }
}