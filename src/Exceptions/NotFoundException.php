<?php

namespace Api\Exceptions;


class NotFoundException extends ApiException
{
    protected $status = 404;
}
