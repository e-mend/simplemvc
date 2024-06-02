<?php

namespace App\Exceptions;

use App\Requests\Json;
use Exception;

final class PermissionException extends Exception
{
    const MESSAGE = 'Permissão negada';

    public function __construct(bool $json = true)
    {
        parent::__construct(self::MESSAGE);
        if ($json) {
            Json::sendError(self::MESSAGE);
        }
    }
}
