<?php

namespace Api\Guards\OAuth2\League\Repositories;

use Api\Guards\OAuth2\League\Entities\RefreshToken as Entity;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Stitch\Model;

class RefreshToken implements RefreshTokenRepositoryInterface
{
    protected $model;

    /**
     * RefreshToken constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new Entity();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $this->model->record([
            'id' => $refreshTokenEntity->getIdentifier(),
            'oauth_access_token_id' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
            'revoked' => false,
            'expires_at' => $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s')
        ])->save();
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        $record = $this->model->find($tokenId);
        $record->revoked = true;
        $record->save();
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $token = $this->model->find($tokenId);

        return $token === null || $token->revoked;
    }
}
