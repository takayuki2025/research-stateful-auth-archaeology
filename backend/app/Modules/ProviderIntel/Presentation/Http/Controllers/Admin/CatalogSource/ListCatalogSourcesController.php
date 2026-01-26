<?php

namespace App\Modules\ProviderIntel\Presentation\Http\Controllers\Admin\CatalogSource;

use App\Http\Controllers\Controller;
use App\Modules\ProviderIntel\Application\UseCase\Admin\CatalogSource\ListCatalogSourcesUseCase;
use Illuminate\Http\Request;

final class ListCatalogSourcesController extends Controller
{
    public function __construct(
        private ListCatalogSourcesUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'provider_id' => 'sometimes|integer|min:1',
            'status' => 'sometimes|string|in:active,inactive',
            'limit' => 'sometimes|integer|min:1|max:200',
            'offset' => 'sometimes|integer|min:0|max:100000',
        ]);

        $items = $this->useCase->handle(
            providerId: isset($data['provider_id']) ? (int)$data['provider_id'] : null,
            status: $data['status'] ?? null,
            limit: (int)($data['limit'] ?? 50),
            offset: (int)($data['offset'] ?? 0),
        );

        return response()->json(['items' => $items], 200);
    }
}