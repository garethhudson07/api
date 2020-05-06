<?php

namespace Api\Resources\Relations;

use Api\Resources\Factory as ResourceFactory;
use Api\Resources\Relations\Stitch\BelongsTo;
use Api\Resources\Relations\Stitch\Has;
use Api\Resources\Resource;
use Api\Container;

class Factory
{
    protected $resourceFactory;

    public function __construct(ResourceFactory $resourceFactory)
    {
        $this->resourceFactory = $resourceFactory;
    }

    /**
     * @param Container $container
     * @return Registry
     */
    public function registry(Container $container)
    {
        return new Registry($container, $this);
    }

    /**
     * @param Resource $resource
     * @return mixed
     */
    public function belongsTo(Resource $resource)
    {
        return $this->make(BelongsTo::class, $resource);
    }

    /**
     * @param Resource $resource
     * @return mixed
     */
    public function has(Resource $resource)
    {
        return $this->make(Has::class, $resource);
    }

    /**
     * @param Resource $resource
     * @return mixed
     */
    public function nest(Resource $resource)
    {
        return $this->make(Nest::class, $resource);
    }

    /**
     * @param string $class
     * @param Resource $resource
     * @return mixed
     */
    protected function make(string $class, Resource $resource)
    {
        return (new $class($resource, $this->resourceFactory->registry()));
    }
}
