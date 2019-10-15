<?php

namespace Api\Resources\Relations;

use Api\Resources\Registry as ResourceRegistry;
use Api\Resources\Relations\Stitch\BelongsTo;
use Api\Resources\Relations\Stitch\Has;
use Api\Resources\Resource;

class Factory
{
    protected $resourceRegistry;

    public function __construct(ResourceRegistry $resourceRegistry)
    {
        $this->resourceRegistry = $resourceRegistry;
    }

    /**
     * @return Registry
     */
    public function registry()
    {
        return new Registry($this);
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
     * @param string $class
     * @param Resource $resource
     * @return mixed
     */
    protected function make(string $class, Resource $resource)
    {
        return (new $class($resource, $this->resourceRegistry));
    }
}
