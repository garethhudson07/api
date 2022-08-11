<?php

namespace Api\Resources;

use Api\Events\Contracts\Emitter as EmitterInterface;
use Api\Events\Payload;
use Api\Pipeline\Pipes\Pipe;
use Api\Repositories\Contracts\Resource as RepositoryInterface;
use Api\Resources\Relations\Registry as Relations;
use Api\Resources\Relations\Relation;
use Api\Schema\Schema;
use Api\Schema\Validation\ValidationException;
use Api\Specs\Contracts\Representations\Factory as RepresentationFactoryInterface;
use Api\Result\Contracts\Collection as ResultCollectionInterface;
use Api\Result\Contracts\Record as ResultRecordInterface;
use Api\Specs\Contracts\Encoder;
use Api\Transformers\Contracts\Transformer as TransformerInterface;
use Exception;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Resource
 * @package Api\Resources
 */
class Resource
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var Relations
     */
    protected $relations;

    /**
     * @var RepresentationFactoryInterface
     */
    protected $representationFactory;

    /**
     * @var TransformerInterface
     */
    protected $transformer;

    /**
     * @var EmitterInterface
     */
    protected $emitter;

    /**
     * @var Endpoints
     */
    protected $endpoints;

    /**
     * @var string
     */
    protected $name;

    /**
     * Resource constructor.
     * @param Schema $schema
     * @param RepositoryInterface $repository
     * @param Relations $relations
     * @param RepresentationFactoryInterface $representationFactory
     * @param TransformerInterface $transformer
     * @param EmitterInterface $emitter
     */
    public function __construct(Schema $schema, RepositoryInterface $repository, Relations $relations, RepresentationFactoryInterface $representationFactory, TransformerInterface $transformer, EmitterInterface $emitter)
    {
        $this->schema = $schema;
        $this->repository = $repository;
        $this->relations = $relations;
        $this->representationFactory = $representationFactory;
        $this->transformer = $transformer;
        $this->emitter = $emitter;
        $this->endpoints = new Endpoints();
    }

    /**
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return TransformerInterface
     */
    public function getTransformer(): TransformerInterface
    {
        return $this->transformer;
    }

    /**
     * @param string $name
     * @return self
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed ...$arguments
     * @return self
     */
    public function except(...$arguments): self
    {
        $this->endpoints->except(...$arguments);

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return self
     */
    public function only(...$arguments): self
    {
        $this->endpoints->only(...$arguments);

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return self
     */
    public function hasMany(...$arguments): self
    {
        $this->relations->include('has', array_merge([$this], $arguments));

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return self
     */
    public function hasOne(...$arguments): self
    {
        $this->relations->include('hasOne', array_merge([$this], $arguments));

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return self
     */
    public function belongsTo(...$arguments): self
    {
        $this->relations->include('belongsTo', array_merge([$this], $arguments));

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return self
     */
    public function nest(...$arguments): self
    {
        $this->relations->include('nest', array_merge([$this], $arguments));

        return $this;
    }

    /**
     * @param string $name
     * @return Relation|null
     */
    public function getRelation(string $name): ?Relation
    {
        return $this->relations->offsetGet($name);
    }

    /**
     * @param string $event
     * @param $listener
     * @return self
     */
    public function listen(string $event, $listener): self
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
     * @return Encoder|null
     * @throws Exception
     */
    public function getRecord(Pipe $pipe, ServerRequestInterface $request): ?Encoder
    {
        $resource = $this;

        $this->endpoints->verify('show');

        $this->emitCrudEvent('readingOne', compact('pipe', 'request', 'resource'));

        $record = $this->repository->getRecord($pipe, $request);

        $this->emitCrudEvent('readOne', compact('pipe', 'request', 'resource', 'record'));

        if (!$record) {
            return null;
        }

        return $this->represent($record);
    }

    /**
     * @param string $action
     * @param array $payloadAttributes
     * @return self
     */
    protected function emitCrudEvent(string $action, array $payloadAttributes): self
    {
        $this->emitter->emit('crud', function (Payload $payload) use ($action, $payloadAttributes) {
            $payload->action($action)->fill($payloadAttributes);
        });

        return $this;
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return Encoder
     * @throws ValidationException
     */
    public function update(Pipe $pipe, ServerRequestInterface $request): Encoder
    {
        $resource = $this;

        $this->endpoints->verify('update');

        $this->emitCrudEvent('updating', compact('pipe', 'request', 'resource'));

        $this->schema->validate($request->getParsedBody());

        $record = $this->repository->update($pipe, $request);

        $this->emitCrudEvent('updated', compact('pipe', 'request', 'resource', 'record'));

        return $this->represent($record);
    }

    /**
     * @param ResultRecordInterface|ResultCollectionInterface $entity
     * @return mixed
     */
    public function createRepresentation(ResultRecordInterface|ResultCollectionInterface $entity)
    {
        if ($entity instanceof ResultRecordInterface) {
            $representation = $this->representationFactory->record($this->getName())->setAttributes(
                $this->transformer->transform($entity)
            );

            $representation->setMeta($this->transformer->transformMetaData($entity));

            foreach ($entity->getRelations() as $key => $relation) {
                $representation->addRelation(
                    $key,
                    $this->getRelation($key)?->createRepresentation($relation)
                );
            }

            return $representation;
        }

        $representation = [];

        foreach ($entity->getItems() as $record) {
            $representation[] = $this->createRepresentation($record);
        }

        return $representation;
    }

    /**
     * @param ResultRecordInterface|ResultCollectionInterface $entity
     * @return Encoder
     */
    protected function represent(ResultRecordInterface|ResultCollectionInterface $entity): Encoder
    {
        $encoder = $this->representationFactory->encoder();

        if ($entity instanceof ResultCollectionInterface && $entity->getMetaData()) {
            $encoder->setMeta($entity->getMetaData());
        }

        return $encoder->setData($this->createRepresentation($entity));
    }
}
