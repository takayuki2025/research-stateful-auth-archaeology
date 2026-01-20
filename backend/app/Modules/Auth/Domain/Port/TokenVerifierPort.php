<?php

namespace App\Modules\Auth\Domain\Port;

use App\Modules\Auth\Domain\Dto\DecodedToken;

interface TokenVerifierPort
{
    public function decode(string $jwt): DecodedToken;
}