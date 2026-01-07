<?php

namespace App\Modules\Item\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class PublishItemDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_origin' => ['required', 'in:USER_PERSONAL,SHOP_MANAGED'],
            'shop_id'     => ['nullable', 'integer'],

            // SHOP_MANAGED のとき shop_id 必須
            // USER_PERSONAL のとき shop_id は null 推奨（ただしUseCaseで最終制御）
            'shop_id'     => ['nullable', 'required_if:item_origin,SHOP_MANAGED', 'integer'],
        ];
    }
}
