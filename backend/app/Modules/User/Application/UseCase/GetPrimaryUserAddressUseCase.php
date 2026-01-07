<?php

namespace App\Modules\User\Application\UseCase;

use App\Modules\User\Domain\Repository\UserAddressRepository;
use DomainException;

final class GetPrimaryUserAddressUseCase
{
    public function __construct(
        private UserAddressRepository $addresses
    ) {
    }

    public function handle(int $userId): array
    {
        $address = $this->addresses->findPrimaryByUser($userId);

        if (! $address) {
            throw new DomainException('Primary address not found.');
        }

        return $address->toArray();
    }
}
