<?php

namespace Api\Exceptions;

use Exception;

class UnknownOperatorException extends Exception
{
    public function __construct(string $operator)
    {
        parent::__construct("The specified operator '$operator' is unknown", 400);
    }
}
