<?php

namespace Api\Exceptions\Handlers;

use Api\Exceptions\Contracts\ApiException as ApiExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Master
{
    protected $delegates;

    protected $response;

    /**
     * Handler constructor.
     * @param Aggregate $delegates
     * @param ResponseInterface $response
     */
    public function __construct(Aggregate $delegates, ResponseInterface $response)
    {
        $this->delegates = $delegates;
        $this->response = $response;
    }

    /**
     * @param Throwable $exception
     * @return \Psr\Http\Message\ResponseInterface
     * @throws Throwable
     */
    public function handle(Throwable $exception)
    {
        if ($response = $this->delegates->handle($exception, $this->response)) {
            return $response;
        }

        if ($exception instanceof ApiExceptionInterface) {
            return $exception->prepareResponse($this->response);
        }

        throw $exception;
    }
}
