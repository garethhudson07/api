<?php

namespace Api\Guards\OAuth2;

use Api\Guards\OAuth2\League\Exceptions\AuthException;
use Api\Guards\OAuth2\Scopes\Collection as Scopes;
use Api\Guards\OAuth2\Scopes\Scope;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;
use Api\Repositories\Contracts\User as UserRepositoryInterface;

class Key
{
    protected $server;

    protected $request;

    protected $userRepository;

    protected $accessTokenId;

    protected $clientId;

    protected $userId;

    protected $user;

    protected $scopes;

    /**
     * Sentinel constructor.
     * @param ResourceServer $server
     * @param ServerRequestInterface $request
     * @param $userRepository
     */
    public function __construct(ResourceServer $server, ServerRequestInterface $request, UserRepositoryInterface $userRepository)
    {
        $this->server = $server;
        $this->request = $request;
        $this->userRepository = $userRepository;
    }

    /**
     * @return $this
     */
    public function handle()
    {
        try {
            return $this->extractOauthAttributes(
                $this->server->validateAuthenticatedRequest($this->request)
            );
        } catch (OAuthServerException $exception) {
            throw (new AuthException())->setBaseException($exception);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return $this
     */
    protected function extractOauthAttributes(ServerRequestInterface $request)
    {
        $this->accessTokenId = $request->getAttribute('oauth_access_token_id');
        $this->clientId = $request->getAttribute('oauth_client_id');
        $this->userId = $request->getAttribute('oauth_user_id');

        $this->scopes = (new Scopes())->fill(array_map(function ($scope)
        {
            return Scope::parse($scope);
        }, $request->getAttribute('oauth_scopes')));

        return $this;
    }

    /**
     * @return null
     */
    public function getUser()
    {
        if (!$this->userId) {
            return null;
        }

        if (!$this->user) {
            $this->user = $this->userRepository->getById($this->userId);
        }

        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getScopes()
    {
        return $this->scopes;
    }
}
