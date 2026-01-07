<?php

use App\Modules\Order\Domain\ValueObject\Address;

final class Order
{
    private ?Address $shippingAddress = null;

    public function withShippingAddress(Address $address): self
    {
        $clone = clone $this;
        $clone->shippingAddress = $address;
        return $clone;
    }

    public function shippingAddress(): Address
    {
        if (!$this->shippingAddress) {
            throw new \DomainException('Shipping address not set');
        }
        return $this->shippingAddress;
    }
}