<?php

namespace App\Modules\Payment\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Domain\Repository\PaymentRepository;
use Illuminate\Http\Request;

final class PaymentReadController extends Controller
{
    public function latestByOrder(Request $request, PaymentRepository $payments)
    {
        $orderId = (int) $request->query('order_id');
        if (! $orderId) {
            return response()->json([
                'message' => 'order_id is required',
            ], 400);
        }

        $payment = $payments->findLatestByOrderId($orderId);

        // ★ 非同期決済では 404 は返さない
        if (! $payment) {
            return response()->json([
                'status' => 'processing',
            ], 202);
        }

        return response()->json([
            'payment_id' => $payment->id(),
            'method' => $payment->method()->value,
            'status' => $payment->status()->value,
            'provider_payment_id' => $payment->providerPaymentId(),
            'instructions' => $payment->instructions(),
            'method_details' => $payment->methodDetails(),
        ], 200);
    }
}
