<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http\Requests\AtlasKernel;

use Illuminate\Foundation\Http\FormRequest;

final class DecideAnalysisRequestHttpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controller 側で policy authorize
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'string', 'in:approved,rejected'],
            'reason'   => ['nullable', 'string', 'max:2000'],
        ];
    }
}
