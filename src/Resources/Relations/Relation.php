<?php

namespace Api\Resources\Relations;

use Api\Resources\Registry as ResourceRegistry;
use Api\Resources\Resource;

/**
 * Class Relation
 * @package Api\Resources\Relations
 */
abstract class Relation
{
    /**
     * @var Resource
     */
    protected $localResource;

    /**
     * @var Resource
     */
    protected $foreignResource;

    /**
     * @var ResourceRegistry
     */
    protected $resourceRegistry;

    /**
     * @var
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $localKey;

    /**
     * @var string|null
     */
    protected $foreignKey;

    /**
     * @var
     */
    protected $binding;

    /**
     * Relation constructor.
     * @param Resource $localResource
     * @param ResourceRegistry $resourceRegistry
     */
    public function __construct(Resource $localResource, ResourceRegistry $resourceRegistry)
    {
        $this->localResource = $localResource;
        $this->resourceRegistry = $resourceRegistry;
    }

    /**
     * @return Resource
     */
    public function getLocalResource()
    {
        return $this->localResource;
    }

    /**
     * @param Resource $model
     * @return $this
     */
    public function foreignResource(Resource $model)
    {
        $this->foreignResource = $model;

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getForeignResource()
    {
        if ($this->foreignResource) {
            return $this->foreignResource;
        }

        if ($this->binding) {
            $this->foreignResource = $this->resourceRegistry->get($this->binding);

            return $this->foreignResource;
        }

        return null;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function name(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function bind(string $name)
    {
        $this->binding = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBinding()
    {
        return $this->binding;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function foreignKey(string $name)
    {
        $this->foreignKey = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function localKey(string $name)
    {
        $this->localKey = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocalKey()
    {
        return $this->localKey;
    }

    /**
     * @return bool
     */
    public function hasKeys()
    {
        return ($this->localKey && $this->foreignKey);
    }

    public function createRepresentation($entity)
    {
        return $this->getForeignResource()->createRepresentation($entity);
    }

    /**
     * @return mixed
     */
    abstract public function boot();
}
