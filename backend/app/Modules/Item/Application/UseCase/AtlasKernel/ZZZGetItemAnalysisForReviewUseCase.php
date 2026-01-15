<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Infrastructure\Persistence\Query\AnalysisResultReadRepository;

final class GetItemAnalysisForReviewUseCase
{
    public function __construct(
        private readonly AnalysisResultReadRepository $repo
    ) {}

    public function handle(int $itemId): array
    {
        $payload = $this->repo->findLatestActiveByItemId($itemId);

        if (! $payload) {
            return [
                'status' => 'not_analyzed',
            ];
        }

        $analysis = $payload['analysis'] ?? [];

        return [
            'status' => 'analyzed',

            // UI が直接使う ViewModel
            'summary' => [
                'brand' => [
                    'value'      => $analysis['integration']['brand_identity']['canonical'] ?? null,
                    'confidence' => $analysis['integration']['brand_identity']['confidence'] ?? null,
                    'source'     => 'ai',
                ],
                'condition' => [
                    'value'      => $analysis['extraction']['condition'][0] ?? null,
                    'confidence' => null,
                    'source'     => 'ai',
                ],
                'color' => [
                    'value'      => $analysis['extraction']['color'][0] ?? null,
                    'confidence' => null,
                    'source'     => 'ai',
                ],
            ],

            'meta' => [
                'model'        => $analysis['lineage']['model'] ?? null,
                'generated_at' => $analysis['lineage']['generated_at'] ?? null,
            ],

            'decision' => $payload['decision'] ?? null,

            'decision_meta' => [
    'decided_by' => $payload['decided_by'] ?? null,
    'decided_user_id' => $payload['decided_user_id'] ?? null,
],
        ];
    }
}