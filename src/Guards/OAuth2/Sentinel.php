<?php

namespace Api\Guards\OAuth2;

use Api\Exceptions\ApiException;
use Api\Pipeline\Pipeline;
use Api\Resources\Resource;
use Psr\Http\Message\ServerRequestInterface;

class Sentinel
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
    public function __construct(ServerRequestInterface $request, Pipeline $pipeline, Key $key)
    {
        $this->request = $request;
        $this->pipeline = $pipeline;
        $this->key = $key;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function protect()
    {
        $this->key->handle();

        $this->checkPipeline()
            ->checkRelations(
                $this->pipeline->last()->getResource(),
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
     * @return $this
     * @throws \Exception
     */
    protected function checkPipeline()
    {
        foreach ($this->pipeline->all() as $pipe) {
            $this->verify($pipe->getOperation(), $pipe->getResource()->getName());
        }

        return $this;
    }

    /**
     * @param Resource $resource
     * @param array $requestRelations
     * @return $this
     * @throws \Exception
     */
    protected function checkRelations(Resource $resource, array $requestRelations)
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
