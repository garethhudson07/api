<?php

namespace Api\Resources;

use Api\Api;
use Api\Pipeline\Pipe;
use Api\Repositories\Contracts\Repository;
use Api\Resources\Relations\Collection as Relations;
use Api\Resources\Relations\HasMany;
use Api\Resources\Relations\Relation;
use Closure;
use Exception;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Resource
 * @package Api\Resources
 */
class Resource
{
    /**
     * @var array
     */
    protected const ENDPOINTS = [];

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var
     */
    protected $name;

    /**
     * @var Relations
     */
    protected $relations;

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
     * Resource constructor.
     * @param $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->relations = new Relations();
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
        $this->addRelation(array_merge([HasMany::class], $arguments));

        return $this;
    }

    /**
     * @param $arguments
     * @return $this
     */
    protected function addRelation($arguments)
    {
        $class = array_shift($arguments);
        $name = array_shift($arguments);

        $this->relations->register(
            $name,
            function () use ($class, $name, $arguments) {
                /** @var Relation $relation */
                /** @noinspection PhpUndefinedMethodInspection */
                $relation = (new $class($this))->name($name);

                if (count($arguments) && $arguments[0] instanceof Closure) {
                    $arguments[0]($relation);
                }

                if (!$relation->getBinding()) {
                    $relation->bind($name);
                }

                return $relation->boot();
            }
        );

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function nest(...$arguments)
    {
        $this->addRelation(array_merge([Relation::class], $arguments));

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

        return Api::getRepresentation()->forCollection($pipe, $request, $this->repository->getCollection($pipe, $request));
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

        return Api::getRepresentation()->forSingleton($pipe, $request, $this->repository->getRecord($pipe, $request));
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

        return Api::getRepresentation()->forSingleton($pipe, $request, $this->repository->create($pipe, $request));
    }
}
