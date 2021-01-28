<?php

/**
 * @noinspection PhpUndefinedMethodInspection
 * @noinspection PhpUndefinedFieldInspection
 */

namespace Api\Guards\OAuth2\League\Repositories;

use Api\Guards\OAuth2\League\Entities\AccessToken as Entity;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Stitch\Model;

/**
 * Class AccessToken
 * @package Api\Guards\OAuth2\League\Repositories
 */
class AccessToken implements AccessTokenRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * AccessToken constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param ClientEntityInterface $clientEntity
     * @param array $scopes
     * @param mixed|null $userIdentifier
     * @return Entity
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): Entity
    {
        return new Entity($clientEntity, $scopes, $userIdentifier);
    }

    /**
     * @param AccessTokenEntityInterface $accessTokenEntity
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $this->model->record([
            'id' => $accessTokenEntity->getIdentifier(),
            'client_id' => $accessTokenEntity->getClient()->getIdentifier(),
            'user_id' => $accessTokenEntity->getUserIdentifier(),
            'expires_at' => $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s')
        ])->save();
    }

    /**
     * @param $tokenId
     */
    public function revokeAccessToken($tokenId)
    {
        $record = $this->model->find($tokenId);
        $record->revoked = true;
        $record->save();
    }

    /**
     * @param $tokenId
     * @return bool
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        $token = $this->model->find($tokenId);

        return $token === null || $token->revoked;
    }
}
