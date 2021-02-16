<?php

namespace Api\Exceptions\Contracts;

use Psr\Http\Message\ResponseInterface;
use Throwable;

interface Handler
{
    /**
     * @param Throwable $exception
     * @return Throwable
     */
    public function process(Throwable $exception): Throwable;

    /**
     * @param Throwable $exception
     * @return bool
     */
    public function respondsTo(Throwable $exception): Bool;

    /**
     * @param Throwable $exception
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function prepareResponse(Throwable $exception, ResponseInterface $response): ResponseInterface;
}