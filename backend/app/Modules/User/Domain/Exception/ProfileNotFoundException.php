<?php

namespace App\Modules\User\Domain\Exception;

use RuntimeException;

final class ProfileNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('User profile not found.');
    }
}
