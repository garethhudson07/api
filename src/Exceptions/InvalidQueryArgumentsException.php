<?php

namespace Api\Exceptions;

use Exception;

class InvalidQueryArgumentsException extends Exception
{
    public function __construct()
    {
        parent::__construct('The arguments provided were not valid', 500);
    }
}
