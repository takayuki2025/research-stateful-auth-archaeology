<?php

namespace App\Modules\Review\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ConfirmReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // v3: AuthPolicy は後で
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}