<?php

namespace App\Modules\Payment\Presentation\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Domain\Account\Repository\BalanceRepository;
use Illuminate\Http\JsonResponse;

final class GetBalanceController extends Controller
{
    public function __construct(
        private BalanceRepository $balances,
    ) {}

    /**
     * GET /api/accounts/{accountId}/balance
     */
    public function __invoke(int $accountId): JsonResponse
    {
        $b = $this->balances->findByAccountId($accountId);

        if (! $b) {
            return response()->json(['message' => 'Balance not found'], 404);
        }

        return response()->json($b, 200);
    }
}