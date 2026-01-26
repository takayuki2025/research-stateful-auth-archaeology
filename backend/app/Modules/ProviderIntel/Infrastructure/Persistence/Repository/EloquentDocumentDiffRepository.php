<?php

namespace App\Modules\ProviderIntel\Infrastructure\Persistence\Repository;

use App\Modules\ProviderIntel\Domain\Repository\DocumentDiffRepository;
use Illuminate\Support\Facades\DB;

final class EloquentDocumentDiffRepository implements DocumentDiffRepository
{
    public function save(?int $projectId, ?int $beforeId, int $afterId, ?array $summary): int
    {
        return (int) DB::table('document_diffs')->insertGetId([
            'project_id' => $projectId,
            'before_document_id' => $beforeId,
            'after_document_id' => $afterId,
            'diff_summary_json' => $summary ? json_encode($summary, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => now(),
        ]);
    }

    public function find(int $id): ?array
    {
        $r = DB::table('document_diffs')->where('id', $id)->first();
        return $r ? (array)$r : null;
    }
}