<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Item;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address' => 'required',
            'payment' => 'required',
            'item_id' => [
                'required',
                'exists:items,id',
                function ($attribute, $value, $fail) {

                    $item = Item::find($value);

                    if ($item && $item->remain <= 0) {
                        $fail('この商品は売り切れです。購入できません。');
                    }
                },
            ],
        ];
    }

        public function messages()
    {
        return [
            'address.required' => '配送先住所が入力されていません。',
            'payment.required' => '支払い方法を選択してください。',
        ];
    }
}
