<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileImageRequest extends FormRequest
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
            'user_image' => 'nullable|mimes:jpeg,png' ,
        ];
    }

        public function messages()
    {
        return [
            'user_image.mimes' => 'ユーザー画像ファイルは.jpegまたは.png形式でアップロードしてください。',
        ];
    }
    protected $redirectRoute = 'profile_edit';
}
