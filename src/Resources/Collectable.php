<?php

namespace Api\Resources;

use Api\Pipeline\Pipes\Pipe;
use Api\Resources\Relations\Registry as Relations;
use Api\Schema\Schema;
use Api\Repositories\Contracts\Resource as RepositoryInterface;
use Api\Specs\Contracts\Representations\Factory as RepresentationFactoryInterface;
use Api\Transformers\Contracts\Transformer as TransformerInterface;
use Api\Events\Contracts\Emitter as EmitterInterface;
use Api\Specs\Contracts\Encoder;
use Exception;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Collectable
 * @package Api\Resources
 */
class Collectable extends Resource
{
    /**
     * Collectable constructor.
     * @param Schema $schema
     * @param RepositoryInterface $repository
     * @param Relations $relations
     * @param RepresentationFactoryInterface $representationFactory
     * @param TransformerInterface $transformer
     * @param EmitterInterface $emitter
     */
    public function __construct(Schema $schema, RepositoryInterface $repository, Relations $relations, RepresentationFactoryInterface $representationFactory, TransformerInterface $transformer, EmitterInterface $emitter)
    {
        parent::__construct($schema, $repository, $relations, $representationFactory, $transformer, $emitter);

        $this->endpoints->add(
            'index',
            'show',
            'create',
            'update',
            'delete'
        );
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return Encoder
     * @throws Exception
     */
    public function getCollection(Pipe $pipe, ServerRequestInterface $request): Encoder
    {
        $resource = $this;

        $this->endpoints->verify('index');

        $this->emitCrudEvent('readingMany', compact('pipe','request', 'resource'));

        $collection = $this->repository->getCollection($pipe, $request);

        $this->emitCrudEvent('readMany', compact('pipe','request', 'resource', 'collection'));

        return $this->represent($collection);
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return Encoder
     * @throws Exception
     */
    public function create(Pipe $pipe, ServerRequestInterface $request): Encoder
    {
        $resource = $this;

        $this->endpoints->verify('create');

        $this->emitCrudEvent('creating', compact('pipe','request', 'resource'));

        $this->schema->validate($request->getParsedBody());

        $record = $this->repository->create($pipe, $request);

        $this->emitCrudEvent('created', compact('pipe','request', 'resource', 'record'));

        return $this->represent($record);
    }

    /**
     * @param Pipe $pipe
     * @return Encoder
     * @throws Exception
     */
    public function delete(Pipe $pipe): Encoder
    {
        $resource = $this;

        $this->endpoints->verify('delete');

        $this->emitCrudEvent('deleting', compact('pipe', 'resource'));

        $record = $this->repository->delete($pipe);

        $this->emitCrudEvent('deleted', compact('pipe', 'resource', 'record'));

        return $this->representationFactory->encoder();
    }
}
