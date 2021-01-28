<?php

namespace Api\Guards\OAuth2;

use Api\Exceptions\ApiException;
use Api\Guards\Contracts\Sentinel as SentinelContract;
use Api\Pipeline\Pipeline;
use Api\Queries\Relations as QueryRelations;
use Api\Resources\Resource;
use Exception;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Sentinel
 * @package Api\Guards\OAuth2
 */
class Sentinel implements SentinelContract
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var Key
     */
    protected $key;

    /**
     * Sentinel constructor.
     * @param ServerRequestInterface $request
     * @param Key $key
     */
    public function __construct(ServerRequestInterface $request, Key $key)
    {
        $this->request = $request;
        $this->key = $key;
    }

    /**
     * @param Pipeline $pipeline
     * @return $this
     * @throws Exception
     */
    public function protect(Pipeline $pipeline): SentinelContract
    {
        $this->key->handle();

        $this->checkPipeline($pipeline);

        if ($this->request->getMethod() === 'GET') {
            $this->checkRelations(
                $pipeline->last()->getResource(),
                $this->request->getAttribute('query')->relations()
            );
        }

        return $this;
    }

    /**
     * @param Pipeline $pipeline
     * @return $this
     * @throws Exception
     */
    protected function checkPipeline(Pipeline $pipeline): self
    {
        foreach ($pipeline->all() as $pipe) {
            $this->verify($pipe->getOperation(), $pipe->getResource()->getName());
        }

        return $this;
    }

    /**
     * @param string $operation
     * @param string $resource
     * @throws Exception
     */
    protected function verify(string $operation, string $resource)
    {
        $scopes = $this->key->getScopes();

        if ($scopes === null || !$scopes->can($operation, $resource)) {
            $this->reject();
        }
    }

    /**
     * @throws Exception
     */
    protected function reject()
    {
        throw new ApiException('access_denied');
    }

    /**
     * @param Resource $resource
     * @param QueryRelations $QueryRelations
     * @return $this
     * @throws Exception
     */
    protected function checkRelations(Resource $resource, QueryRelations $QueryRelations): self
    {
        foreach ($QueryRelations as $requestRelation) {
            $relation = $resource->getRelation($requestRelation->getName());

            if ($relation) {
                $this->verify('read', $relation->getName());
                $this->checkRelations(
                    $relation->getForeignResource(),
                    $requestRelation->getRelations()
                );
            }
        }

        return $this;
    }

    /**
     * @return array|mixed|null
     */
    public function getUser(): ?array
    {
        return $this->key->getUser();
    }

    /**
     * @return int|mixed|string|null
     */
    public function getUserId()
    {
        return $this->key->getUserId();
    }
}
