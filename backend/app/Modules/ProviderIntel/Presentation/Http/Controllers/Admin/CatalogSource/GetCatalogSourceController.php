<?php

namespace App\Modules\ProviderIntel\Presentation\Http\Controllers\Admin\CatalogSource;

use App\Http\Controllers\Controller;
use App\Modules\ProviderIntel\Application\UseCase\Admin\CatalogSource\GetCatalogSourceUseCase;
use Illuminate\Http\Request;

final class GetCatalogSourceController extends Controller
{
    public function __construct(
        private GetCatalogSourceUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request, int $sourceId)
    {
        $item = $this->useCase->handle($sourceId);
        return response()->json($item, 200);
    }
}