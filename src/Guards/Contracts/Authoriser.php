<?php

namespace Api\Guards\Contracts;

use Psr\Http\Message\ResponseInterface;

interface Authoriser
{
    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function authoriseAndPrepareResponse(ResponseInterface $response): ResponseInterface;
}
