<?php

namespace App\Exceptions;

use App\Requests\Json;
use Exception;

final class PermissionException extends Exception
{
    const MESSAGE = 'Permissão negada';

    public function __construct(string $message = self::MESSAGE, int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        Json::sendError(self::MESSAGE);
    }
}
