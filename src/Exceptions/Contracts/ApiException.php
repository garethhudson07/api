<?php

namespace Api\Exceptions\Contracts;

use Psr\Http\Message\ResponseInterface;


interface ApiException
{
    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function prepareResponse(ResponseInterface $response): ResponseInterface;
}