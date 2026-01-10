<?php

namespace App\Modules\Review\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RejectReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
            'note'   => ['nullable', 'string', 'max:1000'],
        ];
    }
}