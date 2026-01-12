<?php

namespace App\Modules\Item\Infrastructure\Persistence\Query;

use Illuminate\Support\Facades\DB;

final class AnalysisRequestQueryRepository
{
    public function listByShopCode(string $shopCode): array
    {
        return DB::select(
            <<<SQL
SELECT
  ar.id,
  ar.item_id,
  ar.status,
  ar.analysis_version,
  ar.created_at,
  rd.decision_type AS decision,
  rd.decided_at
FROM analysis_requests ar
JOIN items i ON i.id = ar.item_id
JOIN shops s ON s.id = i.shop_id
LEFT JOIN (
  SELECT analysis_request_id, MAX(id) AS max_id
  FROM review_decisions
  GROUP BY analysis_request_id
) latest ON latest.analysis_request_id = ar.id
LEFT JOIN review_decisions rd ON rd.id = latest.max_id
WHERE s.shop_code = ?
ORDER BY ar.created_at DESC
SQL,
            [$shopCode]
        );
    }
}