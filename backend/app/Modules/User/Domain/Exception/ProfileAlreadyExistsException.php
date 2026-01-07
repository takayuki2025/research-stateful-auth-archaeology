<?php

namespace App\Modules\User\Domain\Exception;

use RuntimeException;

final class ProfileAlreadyExistsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('User profile already exists.');
    }
}
