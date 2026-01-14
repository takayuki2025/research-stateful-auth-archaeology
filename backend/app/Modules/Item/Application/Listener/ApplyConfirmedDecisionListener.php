<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Listener;

use App\Modules\Item\Domain\Event\Atlas\ReviewDecisionMade;
use App\Modules\Item\Application\UseCase\AtlasKernel\ApplyConfirmedDecisionUseCase;

final class ApplyConfirmedDecisionListener
{
    public function __construct(
        private ApplyConfirmedDecisionUseCase $useCase
    ) {}

    public function handle(ReviewDecisionMade $event): void
    {
        $this->useCase->handle(
            $event->analysisRequestId
        );
    }
}
