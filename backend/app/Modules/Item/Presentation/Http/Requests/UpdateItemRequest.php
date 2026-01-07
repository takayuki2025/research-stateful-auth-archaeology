<?php

namespace App\Modules\Item\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shop_id'    => ['nullable', 'integer'],
            'name'       => ['required', 'string', 'max:20'],
            'price'      => ['required', 'integer', 'min:0'],
            'explain'    => ['required', 'string', 'max:255'],
            'condition'  => ['required', 'string', 'max:20'],
            'category'   => ['required', 'array'],

            // ★ 複数ブランド
            'brands'     => ['nullable', 'array'],
            'brands.*'   => ['string', 'max:50'],

            'item_image' => ['nullable', 'string'],
            'remain'     => ['required', 'integer', 'min:0'],
        ];
    }
}
