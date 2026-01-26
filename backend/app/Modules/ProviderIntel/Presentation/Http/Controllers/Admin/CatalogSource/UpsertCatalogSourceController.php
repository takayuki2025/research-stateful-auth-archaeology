<?php

namespace App\Modules\ProviderIntel\Presentation\Http\Controllers\Admin\CatalogSource;

use App\Http\Controllers\Controller;
use App\Modules\ProviderIntel\Application\UseCase\Admin\CatalogSource\UpsertCatalogSourceUseCase;
use Illuminate\Http\Request;

final class UpsertCatalogSourceController extends Controller
{
    public function __construct(
        private UpsertCatalogSourceUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'id' => 'sometimes|integer|min:1',
            'project_id' => 'sometimes|integer|min:1',
            'provider_id' => 'required|integer|min:1',
            'source_type' => 'required|string|in:html,pdf,manual',
            'source_url' => 'nullable|string',
            'update_frequency' => 'required|string|in:daily,weekly,monthly',
            'status' => 'required|string|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $id = $this->useCase->handle(
            id: isset($data['id']) ? (int)$data['id'] : null,
            projectId: isset($data['project_id']) ? (int)$data['project_id'] : null,
            providerId: (int)$data['provider_id'],
            sourceType: (string)$data['source_type'],
            sourceUrl: $data['source_url'] ?? null,
            updateFrequency: (string)$data['update_frequency'],
            status: (string)$data['status'],
            notes: $data['notes'] ?? null,
        );
\Log::info('[🔥ProviderIntel Upsert] input', $request->all());
        return response()->json(['id' => $id], 200);
    }
}