<?php

namespace App\Exceptions;

use App\Requests\Json;
use Exception;

final class RequestException extends Exception
{
    const MESSAGE = 'Erro ao processar a requisição';

    public function __construct(string $message = self::MESSAGE, int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        Json::sendError(self::MESSAGE);
    }
}
