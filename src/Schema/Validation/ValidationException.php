<?php

namespace Api\Schema\Validation;

use Api\Exceptions\ApiException;

class ValidationException extends ApiException
{
    protected $status = 400;
}
