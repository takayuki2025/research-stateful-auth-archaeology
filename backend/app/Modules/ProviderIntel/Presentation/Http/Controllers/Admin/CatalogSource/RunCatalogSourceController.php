<?php

namespace App\Modules\ProviderIntel\Presentation\Http\Controllers\Admin\CatalogSource;

use App\Http\Controllers\Controller;
use App\Modules\ProviderIntel\Application\UseCase\Admin\CatalogSource\RunCatalogSourceUseCase;
use Illuminate\Http\Request;

final class RunCatalogSourceController extends Controller
{
    public function __construct(
        private RunCatalogSourceUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request, int $sourceId)
    {
        $result = $this->useCase->handle($sourceId);
        return response()->json($result, 200);
    }
}