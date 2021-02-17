<?php

namespace Api\Schema\Validation;

use Api\Exceptions\ApiException;

/**
 * Class ValidationException
 * @package Api\Schema\Validation
 */
class ValidationException extends ApiException
{
    /**
     * @var int
     */
    protected $status = 422;
}
