<?php

namespace App\Modules\Review\Application\UseCase;

use App\Modules\Review\Application\Dto\ReviewDecisionInput;
use App\Modules\Review\Domain\Entity\ReviewDecision;
use App\Modules\Review\Domain\Repository\ReviewDecisionRepository;
use App\Modules\Review\Domain\ValueObject\ReviewDecisionType;
use App\Modules\Review\Domain\ValueObject\ReviewSubject;
use App\Modules\Review\Infrastructure\External\AtlasKernelClient;
use Illuminate\Support\Facades\DB;

final class RejectAndRetryReviewUseCase
{
    public function __construct(
        private ReviewDecisionRepository $decisions,
        private AtlasKernelClient $atlasKernel,
    ) {
    }

    public function handle(int $itemId, ?int $decidedBy, ?string $note): void
{
    $analysis = $this->analysisRepo->getLatest($itemId);

    $this->analysisRepo->markRejected($itemId);

    $this->decisionRepo->saveRejected(
        itemId: $itemId,
        before: $analysis->toArray(),
        decidedBy: $decidedBy,
        note: $note
    );
    }
}