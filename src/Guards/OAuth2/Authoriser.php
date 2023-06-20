<?php

namespace Api\Guards\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Api\Guards\OAuth2\League\RevokeAuthorisationServer;
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
    protected $authServer;

    protected $revokeAuthServer;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * Authoriser constructor.
     * @param AuthorizationServer $server
     * @param ServerRequestInterface $request
     */
    public function __construct(AuthorizationServer $authServer, RevokeAuthorisationServer $revokeAuthServer, ServerRequestInterface $request)
    {
        $this->authServer = $authServer;
        $this->revokeAuthServer = $revokeAuthServer;
        $this->request = $request;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws OAuthServerException
     */
    public function authoriseAndPrepareResponse(ResponseInterface $response): ResponseInterface
    {
        return $this->authServer->respondToAccessTokenRequest($this->request, $response);
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws OAuthServerException
     */
    public function revokeAuthorisationAndPrepareResponse(ResponseInterface $response): ResponseInterface
    {
        return $this->revokeAuthServer->respondToRevokeAccessTokenRequest($this->request, $response);
    }
}
