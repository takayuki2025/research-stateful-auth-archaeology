<?php

namespace App\Modules\Item\Application\UseCase\AtlasKernel;

use Illuminate\Support\Facades\DB;
use App\Modules\Item\Infrastructure\Persistence\Repository\EloquentAnalysisRequestEventRepository;

final class ReviewAnalysisRequestUseCase
{
    public function __construct(
        private EloquentAnalysisRequestEventRepository $events
    ) {}

    public function handle(
        int $requestId,
        string $action,              // approve | reject | escalate
        ?string $selectedValue,
        ?string $note,
        int $reviewerUserId
    ): void {
        DB::transaction(function () use (
            $requestId,
            $action,
            $selectedValue,
            $note,
            $reviewerUserId
        ) {
            $this->events->record(
                requestId: $requestId,
                eventType: "analysis.$action",
                payload: [
                    'selected_value' => $selectedValue,
                    'note'           => $note,
                    'reviewer_id'    => $reviewerUserId,
                ]
            );
        });
    }
}