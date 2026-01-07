<?php

namespace App\Modules\Item\Domain\ValueObject;

class CategoryList
{
    public function __construct(
        private array $categories = [],
    ) {
    }

    public function value(): array
    {
        return $this->categories;
    }

    // ★★★ 追加：toArray() を公開 API にする
    public function toArray(): array
    {
        return $this->categories;
    }
}
