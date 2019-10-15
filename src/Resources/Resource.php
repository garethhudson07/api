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
     * @var
     */
    protected $name;

    /**
     * @var array
     */
    protected $config = [
        'endpoints' => [
            'except' => [],
            'only' => []
        ]
    ];

    /**
     * @var array
     */
    protected const ENDPOINTS = [];

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
        $this->config['endpoints']['except'] = array_merge($this->config['endpoints']['except'], $arguments);

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function only(...$arguments)
    {
        $this->config['endpoints']['only'] = array_merge($this->config['endpoints']['only'], $arguments);

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
        if (!$this->endpointEnabled('index')) {
            throw new Exception("The index endpoint is not available on the $this->name resource");
        }

        return $this->representation->forCollection(
            $pipe->getEntity()->getName(),
            $request,
            $this->repository->getCollection($pipe, $request)
        );
    }

    /**
     * @param string $name
     * @return bool
     */
    public function endpointEnabled(string $name)
    {
        if (!in_array($name, static::ENDPOINTS)) {
            return false;
        }

        if (count($this->config['endpoints']['only']) && !in_array($name, $this->config['endpoints']['only'])) {
            return false;
        }

        if (in_array($name, $this->config['endpoints']['except'])) {
            return false;
        }

        return true;
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return mixed
     * @throws Exception
     */
    public function getRecord(Pipe $pipe, ServerRequestInterface $request)
    {
        if (!$this->endpointEnabled('show')) {
            throw new Exception("The show endpoint is not available on the $this->name resource");
        }

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
        if (!$this->endpointEnabled('create')) {
            throw new Exception("The create endpoint is not available on the $this->name resource");
        }

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
        if (!$this->endpointEnabled('update')) {
            throw new Exception("The update endpoint is not available on the $this->name resource");
        }

        return $this->representation->forSingleton(
            $pipe->getEntity()->getName(),
            $request,
            $this->repository->update($pipe, $request)
        );
    }
}
