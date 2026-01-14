<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use App\Modules\Item\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Item\Domain\Event\Atlas\ReviewDecisionMade;
use Illuminate\Support\Facades\Event;

use Illuminate\Support\Facades\DB;

final class DecideUseCase
{
    public function __construct(
        private ReviewDecisionRepository $decisions,
    ) {}

    public function handle(
        int $analysisRequestId,
        string $decisionType, // approve | edit_confirm | reject
        int $decidedUserId,
        ?array $beforeSnapshot,
        ?array $afterSnapshot,
        ?string $note,
    ): void {
        DB::transaction(function () use (
            $analysisRequestId,
            $decisionType,
            $decidedUserId,
            $beforeSnapshot,
            $afterSnapshot,
            $note
        ) {
            $this->decisions->save([
                'analysis_request_id' => $analysisRequestId,
                'decision_type'       => $decisionType,
                'before_snapshot'     => $beforeSnapshot,
                'after_snapshot'      => $afterSnapshot,
                'decided_by'          => 'human',
                'decided_user_id'     => $decidedUserId,
                'note'                => $note,
                'created_at'          => now(),
            ]);
            Event::dispatch(
    new ReviewDecisionMade($analysisRequestId)
);
        });
    }
}