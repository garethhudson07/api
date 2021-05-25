<?php

namespace Api\Guards\OAuth2\League\Exceptions;

use Api\Exceptions\ApiException;
use Api\Exceptions\Contracts\ApiException as ApiExceptionInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AuthException
 * @package Api\Guards\OAuth2\League\Exceptions
 */
class AuthException extends ApiException implements ApiExceptionInterface
{
    /**
     * @var OAuthServerException
     */
    protected $baseException;

    /**
     * @param OAuthServerException $exception
     * @return $this
     */
    public function setBaseException(OAuthServerException $exception): AuthException
    {
        $this->baseException = $exception;

        $this->payload->data($exception->getPayload());

        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function prepareResponse(ResponseInterface $response): ResponseInterface
    {
        return $this->baseException ? $this->baseException->generateHttpResponse($response) : parent::prepareResponse($response);
    }
}
