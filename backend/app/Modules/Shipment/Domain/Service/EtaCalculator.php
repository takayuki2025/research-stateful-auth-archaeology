<?php

namespace App\Modules\Shipment\Domain\Service;

use Carbon\Carbon;

final class EtaCalculator
{
    public function calculate(array $origin, array $destination): Carbon
    {
        $days = 1;

        if (($origin['region'] ?? null) !== ($destination['region'] ?? null)) {
            $days++;
        }

        if (!empty($destination['is_remote'])) {
            $days++;
        }

        return now()->addDays($days);
    }
}
