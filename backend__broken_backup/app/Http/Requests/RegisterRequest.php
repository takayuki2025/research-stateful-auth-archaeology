<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Id01Test.phpのID1-1に対応
            'name' => ['required', 'string', 'max:255'],
            // Id01Test.phpのID1-2に対応
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // Id01Test.phpのID1-3, ID1-4, ID1-5に対応
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'お名前を入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは８文字以上で入力してください。',
            'password.confirmed' => 'パスワードと一致しません。',
        ];
    }
}