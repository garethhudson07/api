<?php

namespace Api\Guards\OAuth2;

use Api\Guards\Contracts\Sentinel as SentinelContract;
use Api\Exceptions\ApiException;
use Api\Pipeline\Pipeline;
use Api\Resources\Resource;
use Api\Http\Requests\Relations as RequestRelations;
use Psr\Http\Message\ServerRequestInterface;

class Sentinel implements SentinelContract
{
    protected $request;

    protected $pipeline;

    protected $key;

    /**
     * Sentinel constructor.
     * @param ServerRequestInterface $request
     * @param Pipeline $pipeline
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
     */
    public function protect(Pipeline $pipeline)
    {
        $this->key->handle();

        $this->checkPipeline($pipeline)
            ->checkRelations(
                $pipeline->last()->getResource(),
                $this->request->getAttribute('relations')
            );

        return $this;
    }

    /**
     * @return null
     */
    public function getUser()
    {
        return $this->key->getUser();
    }

    /**
     * @param Pipeline $pipeline
     * @return $this
     */
    protected function checkPipeline(Pipeline $pipeline)
    {
        foreach ($pipeline->all() as $pipe) {
            $this->verify($pipe->getOperation(), $pipe->getResource()->getName());
        }

        return $this;
    }

    /**
     * @param Resource $resource
     * @param RequestRelations $requestRelations
     * @return $this
     * @throws \Exception
     */
    protected function checkRelations(Resource $resource, RequestRelations $requestRelations)
    {
        foreach ($requestRelations as $requestRelation) {
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
     * @param string $operation
     * @param string $resource
     * @throws \Exception
     */
    protected function verify(string $operation, string $resource)
    {
        $scopes = $this->key->getScopes();

        if ($scopes === null || !$scopes->can($operation, $resource)) {
            $this->reject();
        }
    }

    /**
     * @throws \Exception
     */
    protected function reject()
    {
        throw new ApiException('access_denied');
    }
}
