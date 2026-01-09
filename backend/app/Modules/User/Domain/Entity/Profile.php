<?php

namespace App\Modules\User\Domain\Entity;

final class Profile
{
    private function __construct(
        private int $userId,
        private string $displayName,
        private ?string $postNumber,
        private ?string $address,
        private ?string $building,
        private ?string $userImage,
    ) {
    }

    public static function reconstitute(
        int $userId,
        string $displayName,
        ?string $postNumber,
        ?string $address,
        ?string $building,
        ?string $userImage,
    ): self {
        return new self($userId, $displayName, $postNumber, $address, $building, $userImage);
    }

    public static function createEmpty(int $userId, string $displayName): self
    {
        return new self($userId, $displayName, null, null, null, null);
    }

    public function userId(): int
    {
        return $this->userId;
    }
    public function displayName(): string
    {
        return $this->displayName;
    }
    public function postNumber(): ?string
    {
        return $this->postNumber;
    }
    public function address(): ?string
    {
        return $this->address;
    }
    public function building(): ?string
    {
        return $this->building;
    }
    public function userImage(): ?string
    {
        return $this->userImage;
    }

    public function withBasic(
        string $displayName,
        ?string $postNumber,
        ?string $address,
        ?string $building
    ): self {
        return new self($this->userId, $displayName, $postNumber, $address, $building, $this->userImage);
    }

    public function withImage(string $path): self
    {
        return new self($this->userId, $this->displayName, $this->postNumber, $this->address, $this->building, $path);
    }

    public function equalsBasic(self $other): bool
    {
        return $this->displayName === $other->displayName
            && $this->postNumber === $other->postNumber
            && $this->address === $other->address
            && $this->building === $other->building;
    }

    public function updateDisplayName(string $name): void
{
    $this->display_name = $name;
    $this->save();
}
}
