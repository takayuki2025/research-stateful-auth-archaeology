<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Application\Dto\AtlasKernel\AtlasReviewDto;
use App\Modules\Item\Application\Query\AtlasReviewQuery;

final class GetAtlasReviewUseCase
{
    public function __construct(
        private AtlasReviewQuery $query,
    ) {}

    public function handle(string $shopCode, int $analysisRequestId): AtlasReviewDto
    {
        $src = $this->query->fetchReviewSource($shopCode, $analysisRequestId);

        $req = $src['request'];
        $before = is_array($src['before']) ? $src['before'] : [];
        $after  = is_array($src['after'])  ? $src['after']  : [];
        $attributes = is_array($src['attributes']) ? $src['attributes'] : [];

        $diff = $this->buildDiff($before, $after);

        // overall_confidence：attributes に confidence が入るようになったら平均等にできる
        $overall = $this->computeOverallConfidence($attributes);

        return new AtlasReviewDto(
            requestId: (int)$req['id'],
            status: (string)$req['status'],
            overallConfidence: $overall,
            before: $before,
            after: $after,
            diff: $diff,
            attributes: $attributes,
        );
    }

    private function buildDiff(array $before, array $after): array
    {
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        $diff = [];

        foreach ($keys as $k) {
            $b = $before[$k] ?? null;
            $a = $after[$k] ?? null;
            if ($b === $a) {
                continue;
            }
            $diff[$k] = [
                'before' => $b,
                'after' => $a,
            ];
        }

        return $diff;
    }

    private function computeOverallConfidence(array $attributes): ?float
    {
        $vals = [];
        foreach ($attributes as $row) {
            $c = $row['confidence'] ?? null;
            if (is_numeric($c)) {
                $vals[] = (float)$c;
            }
        }
        if (count($vals) === 0) return null;

        return array_sum($vals) / count($vals);
    }
}