<?php

namespace App\Http\Controllers;

use App\Models\ItemEntity;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ItemEntityReviewController extends Controller
{
    public function index(Request $request)
    {
        return ItemEntity::query()
            ->where('decision', 'needs_review')
            ->where('is_latest', true)
            ->orderByDesc('confidence')
            ->paginate(20);
    }

    public function approve(int $id)
    {
        abort(
            Response::HTTP_GONE,
            'Legacy entity review is disabled. Use AtlasKernel v3 decision flow.'
        );
    }

    public function reject(int $id)
    {
        abort(
            Response::HTTP_GONE,
            'Legacy entity review is disabled. Use AtlasKernel v3 decision flow.'
        );
    }

    public function reanalyze(Request $request, int $id)
    {
        abort(
            Response::HTTP_GONE,
            'Legacy analysis is disabled. Use AtlasKernel v3.'
        );
    }
}