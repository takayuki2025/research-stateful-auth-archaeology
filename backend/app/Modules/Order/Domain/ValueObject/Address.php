<?php

namespace App\Modules\Order\Domain\ValueObject;

final class Address
{
    public function __construct(
        private ?string $postalCode,
        private ?string $prefecture,
        private ?string $city,
        private ?string $addressLine1,
        private ?string $addressLine2,
        private ?string $recipientName,
        private ?string $phone,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            postalCode: $data['postal_code'],
            prefecture: $data['prefecture'],
            city: $data['city'],
            addressLine1: $data['address_line1'],
            addressLine2: $data['address_line2'] ?? null,
            recipientName: $data['recipient_name'] ?? null,
            phone: $data['phone'] ?? null,
        );
    }

    public function toArray(): array
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
