<?php

namespace Api\Pipeline\Pipes;

use Api\Exceptions\NotFoundException;
use Api\Pipeline\Pipeline;
use Api\Pipeline\Scope;
use Psr\Http\Message\ServerRequestInterface;
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

    protected $scope;

    protected $data = [];

    protected $operation;

    protected $method;

    protected $arguments;

    protected const OPERATION_MAP = [
        'POST' => 'create',
        'GET' => 'read',
        'PUT' => 'update',
        'PATCH' => 'update',
        'DELETE' => 'delete'
    ];

    /**
     * Pipe constructor.
     * @param Pipeline $pipeline
     * @param ServerRequestInterface $request
     */
    public function __construct(Pipeline $pipeline, ServerRequestInterface $request)
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
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function hasKey()
    {
        return ($this->key !== null);
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
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
     * @return bool
     */
    public function isScoped()
    {
        return ($this->scope !== null);
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return $this
     */
    public function call()
    {
        $this->data = $this->getResource()->{$this->method}(...$this->arguments);

        if (is_null($this->data)) {
            throw new NotFoundException('Resource does not exist');
        }

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
     * @return $this
     */
    public function classify()
    {
        $httpMethod = $this->request->getMethod();
        $this->arguments = [$this];

        if (!$this->isLast()) {
            $this->operation = 'read';
            $this->method = 'getByKey';

            return $this;
        }

        $this->arguments[] = $this->request;
        $this->operation = $this::OPERATION_MAP[$httpMethod];

        $this->method = $httpMethod === 'GET'
            ? 'get' . ($this->hasKey() ? 'Record' : 'Collection')
            : $this::OPERATION_MAP[$httpMethod];

        return $this;
    }
}
