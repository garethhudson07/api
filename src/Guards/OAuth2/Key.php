<?php

namespace Api\Guards\OAuth2;

use Api\Guards\Contracts\Key as KeyContract;
use Api\Guards\OAuth2\League\Exceptions\AuthException;
use Api\Guards\OAuth2\Scopes\Collection as Scopes;
use Api\Guards\OAuth2\Scopes\Scope;
use Api\Repositories\Contracts\User as UserRepositoryInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Key
 * @package Api\Guards\OAuth2
 */
class Key implements KeyContract
{
    /**
     * @var ResourceServer
     */
    protected $server;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var string|int|null
     */
    protected $accessTokenId;

    /**
     * @var string|int|null
     */
    protected $clientId;

    /**
     * @var string|int|null
     */
    protected $userId;

    /**
     * @var array|null
     */
    protected $user;

    /**
     * @var array|null
     */
    protected $scopes;

    /**
     * Sentinel constructor.
     * @param ResourceServer $server
     * @param ServerRequestInterface $request
     * @param ?UserRepositoryInterface $userRepository
     */
    public function __construct(ResourceServer $server, ServerRequestInterface $request, ?UserRepositoryInterface $userRepository = null)
    {
        $this->server = $server;
        $this->request = $request;
        $this->userRepository = $userRepository;
    }

    /**
     * @return bool
     */
    public function ready(): bool
    {
        return $this->clientId !== null;
    }

    /**
     * @return $this
     */
    public function handle(): KeyContract
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
    protected function extractOauthAttributes(ServerRequestInterface $request): self
    {
        $this->accessTokenId = $request->getAttribute('oauth_access_token_id');
        $this->clientId = $request->getAttribute('oauth_client_id');
        $this->userId = $request->getAttribute('oauth_user_id');

        $this->scopes = (new Scopes())->fill(array_map(function ($scope) {
            return Scope::parse($scope);
        }, $request->getAttribute('oauth_scopes')));

        return $this;
    }

    /**
     * @return array|null
     */
    public function getUser(): ?array
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
     * @return string|int|null
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return mixed
     */
    public function getScopes(): ?Scopes
    {
        return $this->scopes;
    }
}
