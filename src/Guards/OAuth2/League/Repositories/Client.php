<?php

/**
 * @noinspection PhpMissingParamTypeInspection
 */

namespace Api\Guards\OAuth2\League\Repositories;

use Api\Guards\OAuth2\League\Entities\Client as Entity;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Stitch\Model;
use Stitch\Records\Record;
use Stitch\Result\Record as ResultRecord;


/**
 * Class Client
 * @package Api\Guards\OAuth2\League\Repositories
 */
class Client implements ClientRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $identified = [];

    /**
     * Client constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get a client.
     *
     * @param string $clientIdentifier The client's identifier
     * @return ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier): ClientEntityInterface
    {
        return new Entity($this->fetchClient($clientIdentifier));
    }

    /**
     * @param string $clientIdentifier
     * @return Record|ResultRecord|null
     */
    protected function fetchClient(string $clientIdentifier)
    {
        if (!isset($this->identified[$clientIdentifier])) {
            $this->identified[$clientIdentifier] = $this->model->with('redirects')->where('id', $clientIdentifier)->first();
        }

        return $this->identified[$clientIdentifier];
    }

    /**
     * @param string $clientIdentifier
     * @param string|null $clientSecret
     * @param string|null $grantType
     * @return bool|void
     * @noinspection PhpUndefinedFieldInspection
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $client = $this->fetchClient($clientIdentifier);

        return $client && $clientSecret && $client->secret === $clientSecret;
    }
}
