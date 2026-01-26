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
        // ✅ query params
        $force = filter_var($request->query('force', false), FILTER_VALIDATE_BOOL);
        $reason = $request->query('reason');
        $reason = is_string($reason) && $reason !== '' ? $reason : null;
        
        // ✅ v4.2: mode を受け取り、なければ default='auto'
        $mode = $request->query('mode');
        $mode = is_string($mode) && $mode !== '' ? $mode : 'auto';

        // ✅ allow-list（今のv4.2はこの2つだけで十分）
if (!in_array($mode, ['auto', 'force_ocr'], true)) {
    $mode = 'auto';
}

        $result = $this->useCase->handle(
            sourceId: $sourceId,
            force: $force,
            forceReason: $reason,
            mode: $mode, // UseCaseへ渡す
        );

        return response()->json($result, 200);
    }
}