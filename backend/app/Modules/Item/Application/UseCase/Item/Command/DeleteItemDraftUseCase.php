<?php

namespace App\Modules\Item\Application\UseCase\Item\Command;

use App\Modules\Item\Domain\Repository\ItemDraftRepository;
use App\Modules\Item\Domain\Service\SellerAuthorizationService;
use App\Modules\Auth\Domain\ValueObject\AuthPrincipal;
use DomainException;

final class DeleteItemDraftUseCase
{
    public function __construct(
        private ItemDraftRepository $draftRepository,
        private SellerAuthorizationService $authorization,
    ) {
    }

    public function execute(
        string $draftId,
        AuthPrincipal $principal,
    ): void {
        $draft = $this->draftRepository->findById($draftId);

        if (! $draft) {
            throw new DomainException('Draft not found');
        }

        if (! $this->authorization->canOperate(
            $draft->sellerId(),
            $principal
        )) {
            throw new DomainException('Forbidden');
        }

        $this->draftRepository->delete($draftId);
    }
}
