<?php

namespace App\Modules\Review\Domain\Repository;

interface ReviewDecisionRepository
{
    public function saveConfirmed(
        int $itemId,
        ?array $before,
        array $after,
        ?int $decidedBy,
        ?string $note
    ): void;

    public function saveEdited(
        int $itemId,
        array $before,
        array $after,
        ?int $decidedBy,
        ?string $note
    ): void;

    public function saveRejected(
        int $itemId,
        array $before,
        ?int $decidedBy,
        ?string $note
    ): void;
}
