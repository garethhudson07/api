<?php

namespace Api\Exceptions;

use Api\Exceptions\Contracts\ApiException as ApiExceptionInterface;
use Exception;

class Handler
{
    protected $response;

    /**
     * Handler constructor.
     * @param $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * @param Exception $exception
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function handle(Exception $exception)
    {
        if ($exception instanceof ApiExceptionInterface) {
            return $exception->prepareResponse($this->response);
        }

        throw $exception;
    }
}
