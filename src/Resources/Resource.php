<?php

namespace Api\Resources;

use Api\Pipeline\Pipe;
use Api\Repositories\Contracts\Repository;
use Api\Specs\Contracts\Representation;
use Psr\Http\Message\ServerRequestInterface;
use Api\Resources\Relations\Relation;
use Api\Resources\Relations\Registry as Relations;
use Exception;

/**
 * Class Resource
 * @package Api\Resources
 */
class Resource
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var Relations
     */
    protected $relations;

    /**
     * @var Representation
     */
    protected $representation;

    /**
     * @var array
     */
    protected $endpoints;

    /**
     * @var
     */
    protected $name;

    /**
     * Resource constructor.
     * @param Repository $repository
     * @param Relations $relations
     * @param Representation $representation
     */
    public function __construct(Repository $repository, Relations $relations, Representation $representation)
    {
        $this->repository = $repository;
        $this->relations = $relations;
        $this->representation = $representation;
        $this->endpoints = new Endpoints();
    }

    /**
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
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
     * @param mixed ...$arguments
     * @return $this
     */
    public function except(...$arguments)
    {
        $this->endpoints->except(...$arguments);

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function only(...$arguments)
    {
        $this->endpoints->only(...$arguments);

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function hasMany(...$arguments)
    {
        $this->relations->has(...array_merge([$this], $arguments));

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function belongsTo(...$arguments)
    {
        $this->relations->belongsTo(...array_merge([$this], $arguments));

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function nest(...$arguments)
    {
        $this->relations->nest(...array_merge([$this], $arguments));

        return $this;
    }

    /**
     * @param string $name
     * @return Relation|null
     */
    public function getRelation(string $name)
    {
        return $this->relations->get($name);
    }
    
    /**
     * @param Pipe $pipe
     * @return mixed
     */
    public function getByKey(Pipe $pipe)
    {
        return $this->repository->getByKey($pipe);
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return mixed
     * @throws Exception
     */
    public function getCollection(Pipe $pipe, ServerRequestInterface $request)
    {
        $this->endpoints->verify('index');

        return $this->representation->forCollection(
            $pipe->getEntity()->getName(),
            $request,
            $this->repository->getCollection($pipe, $request)
        );
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return mixed
     * @throws Exception
     */
    public function getRecord(Pipe $pipe, ServerRequestInterface $request)
    {
        $this->endpoints->verify('show');

        return $this->representation->forSingleton(
            $pipe->getEntity()->getName(),
            $request,
            $this->repository->getRecord($pipe, $request)
        );
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return mixed
     * @throws Exception
     */
    public function create(Pipe $pipe, ServerRequestInterface $request)
    {
        $this->endpoints->verify('create');

        return $this->representation->forSingleton(
            $pipe->getEntity()->getName(),
            $request,
            $this->repository->create($pipe, $request)
        );
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return mixed
     * @throws Exception
     */
    public function update(Pipe $pipe, ServerRequestInterface $request)
    {
        $this->endpoints->verify('update');

        return $this->representation->forSingleton(
            $pipe->getEntity()->getName(),
            $request,
            $this->repository->update($pipe, $request)
        );
    }
}
