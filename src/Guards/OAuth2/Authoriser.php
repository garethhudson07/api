<?php

namespace Api\Guards\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Authoriser
{
    protected $server;

    protected $request;

    public function __construct(AuthorizationServer $server, ServerRequestInterface $request)
    {
        $this->server = $server;
        $this->request = $request;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function authoriseAndPrepareResponse(ResponseInterface $response): ResponseInterface
    {
        try {
            return $this->server->respondToAccessTokenRequest($this->request, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    }
}
