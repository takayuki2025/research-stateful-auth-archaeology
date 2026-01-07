<?php

namespace App\Modules\Order\Application\UseCase;

use App\Modules\Order\Domain\Repository\OrderRepository;
use App\Modules\Order\Domain\ValueObject\Address;
use App\Models\UserAddress;
use DateTimeImmutable;
use DomainException;

final class ConfirmOrderAddressUseCase
{
    public function __construct(
        private OrderRepository $orders
    ) {
    }

    public function handle(
        int $orderId,
        int $userId,
        int $addressId
    ): void {
        // ① 自分の「未確定注文」のみ取得
        $order = $this->orders->findDraftByUser($orderId, $userId);

        if (! $order) {
            throw new DomainException('Order not found or not editable');
        }

        // ② user_addresses から取得（★ shop_addresses は絶対NG）
        $userAddress = UserAddress::where('id', $addressId)
            ->where('user_id', $userId)
            ->first();

        if (! $userAddress) {
            throw new DomainException('Address not found');
        }

        // ③ ValueObject 化（唯一の正）
        $address = new Address(
            postalCode: $userAddress->post_number,
            prefecture: $userAddress->prefecture,
            city: $userAddress->city,
            addressLine1: $userAddress->address_line1,
            addressLine2: $userAddress->address_line2,
            recipientName: $userAddress->recipient_name,
            phone: $userAddress->phone,
        );

        // ④ Order に snapshot
        $order->confirmAddress(
            $address,
            new DateTimeImmutable()
        );

        // ⑤ 永続化
        $this->orders->save($order);
    }
}
