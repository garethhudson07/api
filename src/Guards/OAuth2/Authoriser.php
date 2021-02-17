<?php

namespace Api\Guards\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Authoriser
 * @package Api\Guards\OAuth2
 */
class Authoriser
{
    /**
     * @var AuthorizationServer
     */
    protected $server;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * Authoriser constructor.
     * @param AuthorizationServer $server
     * @param ServerRequestInterface $request
     */
    public function __construct(AuthorizationServer $server, ServerRequestInterface $request)
    {
        $this->server = $server;
        $this->request = $request;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws OAuthServerException
     */
    public function authoriseAndPrepareResponse(ResponseInterface $response): ResponseInterface
    {
        return $this->server->respondToAccessTokenRequest($this->request, $response);
    }
}
