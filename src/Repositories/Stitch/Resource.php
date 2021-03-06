<?php

namespace Api\Repositories\Stitch;

use Api\Pipeline\Pipes\Pipe;
use Api\Repositories\Contracts\Resource as RepositoryInterface;
use Api\Repositories\Stitch\Bridge\QueryResolver;
use Api\Repositories\Stitch\Bridge\RelationshipResolver;
use Exception;
use Stitch\Model;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Repository
 * @package Api\Repositories\Stitch
 */
class Resource implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * Repository constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     */
    protected function associateRelationships(Pipe $pipe, ServerRequestInterface $request)
    {
        RelationshipResolver::associate(
            $this->model,
            $pipe->getResource(),
            $request->getAttribute('query')->relations()
        );
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param Pipe $pipe
     * @return array|null
     */
    public function getByKey(Pipe $pipe): ?array
    {
        $record = (new QueryResolver(
            $this->model,
            $pipe
        ))->byKey();

        return $record ? $record->toArray() : null;
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return array
     */
    public function getCollection(Pipe $pipe, ServerRequestInterface $request): array
    {
        $this->associateRelationships($pipe, $request);

        return (new QueryResolver(
            $this->model,
            $pipe
        ))->collection($request)->toArray();
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return array|null
     */
    public function getRecord(Pipe $pipe, ServerRequestInterface $request): ?array
    {
        $this->associateRelationships($pipe, $request);

        $record = (new QueryResolver(
            $this->model,
            $pipe
        ))->record($request);

        return $record ? $record->toArray() : null;
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return array
     * @throws Exception
     */
    public function create(Pipe $pipe, ServerRequestInterface $request): array
    {
        return $this->model->record(
            $request->getParsedBody()['data']['attributes'] ?? []
        )->save()->toArray();
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return array
     * @throws Exception
     */
    public function update(Pipe $pipe, ServerRequestInterface $request): array
    {
        return (new QueryResolver(
            $this->model,
            $pipe
        ))->byKey()->hydrate()->fill(
            $request->getParsedBody()['data']['attributes'] ?? []
        )->save()->toArray();
    }

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function delete(Pipe $pipe): array
    {
        $record = (new QueryResolver(
            $this->model,
            $pipe
        ))->byKey()->hydrate();

        $record->delete();

        return $record->toArray();
    }
}
