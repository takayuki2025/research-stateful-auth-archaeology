<?php

namespace App\Modules\ProviderIntel\Presentation\Http\Controllers\Admin\Documents;

use App\Http\Controllers\Controller;
use App\Modules\ProviderIntel\Domain\Repository\ExtractedDocumentRepository;
use Illuminate\Http\Request;

final class GetExtractedDocumentController extends Controller
{
    public function __construct(
        private ExtractedDocumentRepository $docs,
    ) {}

    public function __invoke(Request $request, int $id)
    {
        $doc = $this->docs->find($id);
        if (!$doc) return response()->json(['message'=>'Not found'], 404);

        return response()->json($doc, 200);
    }
}