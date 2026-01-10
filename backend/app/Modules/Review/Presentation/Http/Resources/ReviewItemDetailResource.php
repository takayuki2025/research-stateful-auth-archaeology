<?php

namespace App\Modules\Review\Presentation\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ReviewItemDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return $this->resource;
    }
}