<?php

namespace Api\Guards\OAuth2\League\Exceptions;

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
     * @return OAuthServerException
     */
    public function getBaseException(): OAuthServerException
    {
        return $this->baseException;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function prepareResponse(ResponseInterface $response): ResponseInterface
    {
        return $this->baseException->generateHttpResponse($response);
    }
}
