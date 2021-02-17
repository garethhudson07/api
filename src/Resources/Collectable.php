<?php

namespace Api\Resources;

use Api\Pipeline\Pipes\Pipe;
use Api\Resources\Relations\Registry as Relations;
use Api\Schema\Schema;
use Api\Repositories\Contracts\Resource as RepositoryInterface;
use Api\Specs\Contracts\Representation;
use Api\Events\Contracts\Emitter as EmitterInterface;
use Exception;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Collection
 * @package Api\Resources
 */
class Collectable extends Resource
{
    /**
     * Collectable constructor.
     * @param Schema $schema
     * @param RepositoryInterface $repository
     * @param Relations $relations
     * @param Representation $representation
     * @param EmitterInterface $emitter
     */
    public function __construct(Schema $schema, RepositoryInterface $repository, Relations $relations, Representation $representation, EmitterInterface $emitter)
    {
        parent::__construct($schema, $repository, $relations, $representation, $emitter);

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
     * @return mixed
     * @throws Exception
     */
    public function getCollection(Pipe $pipe, ServerRequestInterface $request)
    {
        $this->endpoints->verify('index');
        $this->emitCrudEvent('readingMany', compact('pipe','request'));

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
    public function create(Pipe $pipe, ServerRequestInterface $request)
    {
        $this->endpoints->verify('create');
        $this->emitCrudEvent('creating', compact('pipe','request'));
        $this->schema->validate($request->getParsedBody()['data']['attributes'] ?? []);

        $record = $this->repository->create($pipe, $request);

        $this->emitCrudEvent('created', compact('record'));

        return $this->representation->forSingleton(
            $pipe->getEntity()->getName(),
            $request,
            $record
        );
    }

    /**
     * @param Pipe $pipe
     * @return mixed
     * @throws Exception
     */
    public function delete(Pipe $pipe): array
    {
        $this->endpoints->verify('delete');
        $this->emitCrudEvent('deleting', compact('pipe'));

        $record = $this->repository->delete($pipe);

        $this->emitCrudEvent('deleted', compact('record'));

        return [];
    }
}
