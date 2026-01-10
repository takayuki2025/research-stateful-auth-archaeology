<?php

namespace App\Modules\Review\Infrastructure\Persistence;

use App\Modules\Review\Domain\Repository\ReviewDecisionRepository;
use Illuminate\Support\Facades\DB;

final class EloquentReviewDecisionRepository implements ReviewDecisionRepository
{
    public function saveConfirmed(
        int $itemId,
        ?array $before,
        array $after,
        ?int $decidedBy,
        ?string $note
    ): void {
        $this->insert(
            $itemId,
            'confirm',
            $before,
            $after,
            $decidedBy,
            $note
        );
    }

    public function saveEdited(
        int $itemId,
        array $before,
        array $after,
        ?int $decidedBy,
        ?string $note
    ): void {
        $this->insert(
            $itemId,
            'edit_confirm',
            $before,
            $after,
            $decidedBy,
            $note
        );
    }

    public function saveRejected(
        int $itemId,
        array $before,
        ?int $decidedBy,
        ?string $note
    ): void {
        $this->insert(
            $itemId,
            'reject',
            $before,
            null,
            $decidedBy,
            $note
        );
    }

    private function insert(
        int $itemId,
        string $type,
        ?array $before,
        ?array $after,
        ?int $decidedBy,
        ?string $note
    ): void {
        DB::table('review_decisions')->insert([
            'item_id'         => $itemId,
            'decision_type'   => $type,
            'before_snapshot' => $before ? json_encode($before) : null,
            'after_snapshot'  => $after ? json_encode($after) : null,
            'decided_by'      => $decidedBy,
            'note'            => $note,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }
}