<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'post_number' => 'required||regex:/^\d{3}-\d{4}$/',
            'address' => ['required'],
        ];
    }


    public function messages()
    {
        return [
            'name.required' => '名前を入力してください。',
            'name.string' => '名前を文字列で入力してください。',
            'name.max' => '名前は20文字以内で入力してください。',
            'post_number.required' => '郵便番号を入力してください。',
            'post_number.regex' => 'ハイフンありの８桁で入力してください。',
            'address.required' => '住所を入力してください。',
        ];
    }
    protected $redirectRoute = 'profile_edit';
}
