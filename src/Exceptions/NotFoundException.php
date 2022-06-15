<?php

namespace Api\Exceptions;


class NotFoundException extends ApiException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 400);
    }
}
