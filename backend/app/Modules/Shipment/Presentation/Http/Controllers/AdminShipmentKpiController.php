<?php


namespace App\Modules\Shipment\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class AdminShipmentKpiController
{
    public function __invoke(): JsonResponse
    {
        $row = DB::table('shipments')
            ->selectRaw('
                COUNT(*) as total_shipments,
                SUM(status = "delivered") as delivered,
                SUM(eta < NOW() AND status != "delivered") as delayed
            ')
            ->first();

        $avgEta = DB::table('shipments')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->selectRaw('AVG(DATEDIFF(eta, created_at)) as avg_eta_days')
            ->value('avg_eta_days');

        return response()->json([
            'total_shipments' => (int)$row->total_shipments,
            'delivered' => (int)$row->delivered,
            'delayed' => (int)$row->delayed,
            'avg_eta_days' => round($avgEta, 2),
        ]);
    }
}
