<?php

namespace App\Modules\User\Domain\Entity;

final class UserAddress
{
    public function __construct(
        private int $id,
        private int $userId,
        private string $postalCode,
        private string $prefecture,
        private string $city,
        private string $addressLine1,
        private ?string $addressLine2,
        private string $recipientName,
        private ?string $phone,
        private bool $isPrimary,
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }
    public function userId(): int
    {
        return $this->userId;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function toSnapshot(): array
    {
        return [
            'postal_code' => $this->postalCode,
            'prefecture' => $this->prefecture,
            'city' => $this->city,
            'address_line1' => $this->addressLine1,
            'address_line2' => $this->addressLine2,
            'recipient_name' => $this->recipientName,
            'phone' => $this->phone,
        ];
    }
}
