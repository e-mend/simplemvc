<?php

namespace App\Exceptions;

use Exception;

final class LoginException extends Exception
{
    public function __construct(string $message = "", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
