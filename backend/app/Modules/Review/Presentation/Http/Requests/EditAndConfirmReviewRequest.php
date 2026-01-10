<?php

namespace App\Modules\Review\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class EditAndConfirmReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:1000'],
            'tags' => ['required', 'array'],
            'tags.*' => ['array'],
            'tags.*.*.entity_id' => ['nullable', 'integer', 'min:1'],
            'tags.*.*.display_name' => ['required', 'string', 'max:255'],
            'tags.*.*.confidence' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ];
    }
}