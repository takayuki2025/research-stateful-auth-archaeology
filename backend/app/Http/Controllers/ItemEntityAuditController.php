<?php

namespace App\Http\Controllers;

use App\Models\ItemEntityAudit;

class ItemEntityAuditController extends Controller
{
    public function index(int $itemEntityId)
    {
        return ItemEntityAudit::query()
            ->where('item_entity_id', $itemEntityId)
            ->orderByDesc('id')
            ->get();
    }
}
