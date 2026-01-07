<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'name' => 'required|max:20',
            'brand' => 'max:20',
            'price' => 'required|numeric|min:100|max:2000000000',
            'explain' => 'required|max:255',
            'condition' => ['required'],
            'category' => ['required', 'array'],
            'item_image' => 'required',
            ];
    }
        public function messages()
    {
        return [
            'name.required' => '商品名を入力してください。',
            'name.max' => '名前を20文字以下で入力してください。',
            'brand.max' => 'ブランド名は20文字以下で入力してください。',
            'price.required' => '金額を入力してください。',
            'price.numeric' => '数値で入力してください。',
            'price.min' => '１００円以上の金額で入力してください。',
            'price.max' => '２０億円以下の金額で入力してください。',
            'explain.required' => '商品説明を入力してください。',
            'explain.max' => '商品説明を２５５文字以内で入力してください。',
            'condition.required' => '商品状態を選択してください。',
            'category.required' => 'カテゴリーを選択してください。',
            'category.*.string' => 'カテゴリーを選択してください。',
            'item_image.required' => '商品画像ファイルをアップロードしてください。',
        ];
    }

}
