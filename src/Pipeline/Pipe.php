<?php

namespace Api\Pipeline;

use Api\Requests\Request;
use Api\Resources\Singleton;
use Api\Resources\Collectable;
use Api\Resources\Relations\Relation;
use Api\Resources\Resource;

class Pipe
{
    protected $request;

    protected $pipeline;

    protected $entity;

    protected $key;

    protected $method;

    protected $arguments = [];

    protected $scope;

    protected $data = [];

    /**
     * Pipe constructor.
     * @param Pipeline $pipeline
     * @param Request $request
     */
    public function __construct(Pipeline $pipeline, Request $request)
    {
        $this->pipeline = $pipeline;
        $this->request = $request;
    }

    /**
     * @param $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return Resource|null
     */
    public function getResource()
    {
        return $this->entity instanceof Relation ? $this->entity->getForeignResource() : $this->entity;
    }

    /**
     * @return bool
     */
    public function isSingleton()
    {
        return ($this->getResource() instanceof Singleton);
    }

    /**
     * @return bool
     */
    public function isCollectable()
    {
        return ($this->getResource() instanceof Collectable);
    }

    /**
     * @param $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasKey()
    {
        return ($this->key !== null);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param Pipe $pipe
     * @return $this
     */
    public function scope(Pipe $pipe)
    {
        $this->scope = new Scope($pipe, $this->entity);

        return $this;
    }

    /**
     * @return $this
     */
    public function call()
    {
        $this->data = $this->getResource()->{$this->resolveMethod()}(...$this->resolveArguments());

        return $this;
    }

    /**
     * @return array
     */
    public function ancestors()
    {
        return $this->pipeline->before($this);
    }

    /**
     * @return array
     */
    public function descendants()
    {
        return $this->pipeline->after($this);
    }

    /**
     * @return bool
     */
    public function isLast()
    {
        return ($this->pipeline->last() === $this);
    }

    /**
     * @return array
     */
    protected function resolveArguments()
    {
        return [$this, $this->request];
    }

    /**
     * @return string
     */
    protected function resolveMethod()
    {
        switch ($this->request->method()) {
            case 'POST':
                return 'create';
            case 'PUT';
                return 'update';
            case 'DELETE';
                return 'destroy';
            default:
                if ($this->isLast()) {
                    return 'get' . ($this->key ? 'Record' : 'Collection');
                }

                return 'getByKey';
        }
    }
}