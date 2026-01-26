<?php

namespace App\Modules\ProviderIntel\Presentation\Http\Controllers\Admin\Documents;

use App\Http\Controllers\Controller;
use App\Modules\ProviderIntel\Domain\Repository\DocumentDiffRepository;
use Illuminate\Http\Request;

final class GetDocumentDiffController extends Controller
{
    public function __construct(
        private DocumentDiffRepository $diffs,
    ) {
    }

    public function __invoke(Request $request, int $id)
    {
        $diff = $this->diffs->find($id);
        if (!$diff) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($diff, 200);
    }
}