<?php

namespace App\Modules\Item\Infrastructure\External;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class HttpAtlasKernelClient
{
    /**
     * HTTPでAtlasKernelへ解析依頼し、Laravel側が期待する「ローカル形式」に整形して返す。
     *
     * @param array<string,mixed> $context
     */
    public function analyze(
        int $itemId,
        string $rawText,
        ?int $tenantId = null,
        array $context = []
    ): array {
        $endpoint = config('atlaskernel.endpoint');
        $timeout  = (int) (config('atlaskernel.timeout') ?? 10);

        // ✅ context を merge（brand_text は上書き可能）
        $payload = [
            'project_id' => 'occore',
            'task_type'  => 'entity_extract',
            'raw_text'   => $rawText,
            'mode'       => 2,
            'context'    => array_merge([
                'tenant_id' => $tenantId,
                'item_id'   => $itemId,
            ], $context),
        ];

        // ✅ brand_text が無い/空なら rawText を fallback（最後の手段）
        if (
            !array_key_exists('brand_text', $payload['context']) ||
            $payload['context']['brand_text'] === null ||
            trim((string)$payload['context']['brand_text']) === ''
        ) {
            $payload['context']['brand_text'] = $rawText;
        }
logger()->info('[🔥AtlasKernelHTTP] payload', [
  'item_id' => $itemId,
  'raw_text' => $payload['raw_text'],
  'brand_text' => $payload['context']['brand_text'] ?? null,
]);
        $res = Http::timeout($timeout)
            ->acceptJson()
            ->asJson()
            ->post($endpoint, $payload);

        if (! $res->ok()) {
            throw new RuntimeException('AtlasKernel HTTP failed: '.$res->status().' '.$res->body());
        }

        $json = $res->json();

        $items = data_get($json, 'result.items', []);
        if (!is_array($items)) {
            throw new RuntimeException('AtlasKernel HTTP invalid response: result.items missing');
        }

        $brand     = $this->findByType($items, 'brand');
        $condition = $this->findByType($items, 'condition');
        $color     = $this->findByType($items, 'color');

        $brandName = $brand['canonical_value'] ?? null;
        $condName  = $condition['canonical_value'] ?? null;
        $colorName = $color['canonical_value'] ?? null;

        $brandConf = isset($brand['confidence']) ? (float)$brand['confidence'] : 0.0;
        $condConf  = isset($condition['confidence']) ? (float)$condition['confidence'] : 0.0;
        $colorConf = isset($color['confidence']) ? (float)$color['confidence'] : 0.0;

        $confidenceMap = [
            'brand'     => $brandName ? $brandConf : 0.0,
            'condition' => $condName ? $condConf : 0.0,
            'color'     => $colorName ? $colorConf : 0.0,
        ];

        $overall = max(
            $confidenceMap['brand'],
            $confidenceMap['condition'],
            $confidenceMap['color']
        );

        // ✅ 既存Jobが期待する “ローカル形式” へ整形して返す
        return [
            'brand' => [
                'name'       => $brandName,
                'confidence' => $confidenceMap['brand'],
            ],
            'condition' => [
                'name'       => $condName,
                'confidence' => $confidenceMap['condition'],
            ],
            'color' => [
                'name'       => $colorName,
                'confidence' => $confidenceMap['color'],
            ],
            'tokens' => [
                // raw_value を最低限 token として残す（将来改善）
                'brand'     => $brand && isset($brand['raw_value']) ? [$brand['raw_value']] : [],
                'condition' => $condition && isset($condition['raw_value']) ? [$condition['raw_value']] : [],
                'color'     => $color && isset($color['raw_value']) ? [$color['raw_value']] : [],
            ],
            'confidence_map'     => $confidenceMap,
            'overall_confidence' => $overall,
        ];
    }

    /** @param array<int, mixed> $items */
    private function findByType(array $items, string $type): ?array
    {
        foreach ($items as $it) {
            if (is_array($it) && ($it['entity_type'] ?? null) === $type) {
                return $it;
            }
        }
        return null;
    }
}
