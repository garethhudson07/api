<?php

namespace Api\Resources;

use Api\Events\Payload;
use Api\Pipeline\Pipes\Pipe;
use Api\Schema\Schema;
use Api\Repositories\Contracts\Resource as RepositoryInterface;
use Api\Specs\Contracts\Representation;
use Psr\Http\Message\ServerRequestInterface;
use Api\Resources\Relations\Relation;
use Api\Resources\Relations\Registry as Relations;
use Api\Events\Contracts\Emitter as EmitterInterface;
use Exception;

/**
 * Class Resource
 * @package Api\Resources
 */
class Resource
{
    protected $schema;

    /**
     * @var Resource
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

    protected $emitter;

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
     * @param Schema $schema
     * @param RepositoryInterface $repository
     * @param Relations $relations
     * @param Representation $representation
     * @param EmitterInterface $emitter
     */
    public function __construct(Schema $schema, RepositoryInterface $repository, Relations $relations, Representation $representation, EmitterInterface $emitter)
    {
        $this->schema = $schema;
        $this->repository = $repository;
        $this->relations = $relations;
        $this->representation = $representation;
        $this->emitter = $emitter;
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
        $this->relations->include('has', array_merge([$this], $arguments));

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function belongsTo(...$arguments)
    {
        $this->relations->include('belongsTo', array_merge([$this], $arguments));

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function nest(...$arguments)
    {
        $this->relations->include('nest', array_merge([$this], $arguments));

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
     * @param string $event
     * @param $listener
     * @return $this
     */
    public function listen(string $event, $listener)
    {
        $this->emitter->listen($event, $listener);

        return $this;
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
    public function getRecord(Pipe $pipe, ServerRequestInterface $request)
    {
        $this->endpoints->verify('show');
        $this->emitCrudEvent('readingOne', compact('pipe','request'));

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
    public function update(Pipe $pipe, ServerRequestInterface $request)
    {
        $this->endpoints->verify('update');
        $this->emitCrudEvent('updating', compact('pipe','request'));
        $this->schema->validate($request->getParsedBody());

        $record = $this->repository->update($pipe, $request);

        $this->emitCrudEvent('updated', compact('record'));

        return $this->representation->forSingleton(
            $pipe->getEntity()->getName(),
            $request,
            $this->repository->update($pipe, $request)
        );
    }

    /**
     * @param string $action
     * @param array $payloadAttributes
     * @return $this
     */
    protected function emitCrudEvent(string $action, array $payloadAttributes)
    {
        $this->emitter->emit('crud', function (Payload $payload) use ($action, $payloadAttributes)
        {
            $payload->action($action)->fill($payloadAttributes);
        });

        return $this;
    }
}
