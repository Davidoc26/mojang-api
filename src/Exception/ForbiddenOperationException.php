<?php
declare(strict_types=1);


namespace Davidoc26\MojangAPI\Exception;


use Throwable;

class ForbiddenOperationException extends MojangAPIException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }
}