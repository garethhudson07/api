<?php

namespace Api\Auth\OAuth2\League\Exceptions;

use Api\Exceptions\Contracts\ApiException as ApiExceptionInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class AuthException extends RuntimeException implements ApiExceptionInterface
{
    protected $baseException;

    /**
     * @param OAuthServerException $exception
     * @return $this
     */
    public function setBaseException(OAuthServerException $exception)
    {
        $this->baseException = $exception;

        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function formatResponse(ResponseInterface $response): ResponseInterface
    {
        return $this->baseException->generateHttpResponse($response);
    }
}
