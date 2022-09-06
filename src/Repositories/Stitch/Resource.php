<?php

namespace Api\Repositories\Stitch;

use Api\Pipeline\Pipes\Pipe;
use Api\Repositories\Contracts\Resource as RepositoryInterface;
use Api\Repositories\Stitch\Bridge\QueryResolver;
use Api\Repositories\Stitch\Bridge\RelationshipResolver;
use Api\Result\Stitch\Set as ResultSet;
use Api\Result\Stitch\Record as ResultRecord;
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
            $request->getAttribute('parsedQuery')->getRelations()
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
    public function getByKey(Pipe $pipe): ?ResultRecord
    {
        $record = (new QueryResolver(
            $this->model,
            $pipe
        ))->byKey();

        return $record ? new ResultRecord($record) : null;
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return ResultSet
     */
    public function getCollection(Pipe $pipe, ServerRequestInterface $request): ResultSet
    {
        $this->associateRelationships($pipe, $request);

        return (new ResultSet())->build(
            (new QueryResolver(
                $this->model,
                $pipe
            ))->collection($request)
        );
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return array|null
     */
    public function getRecord(Pipe $pipe, ServerRequestInterface $request): ?ResultRecord
    {
        $this->associateRelationships($pipe, $request);

        $record = (new QueryResolver(
            $this->model,
            $pipe
        ))->record($request);

        return $record ? new ResultRecord($record) : null;
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return ResultRecord
     * @throws Exception
     */
    public function create(Pipe $pipe, ServerRequestInterface $request): ResultRecord
    {
        return new ResultRecord(
            $this->model->record(
                $pipe->getResource()->getTransformer()->reverse($request->getParsedBody()->toArray())
            )->save()
        );
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return array
     * @throws Exception
     */
    public function update(Pipe $pipe, ServerRequestInterface $request): ResultRecord
    {
        return new ResultRecord(
            (new QueryResolver(
                $this->model,
                $pipe
            ))->byKey()->hydrate()->fill(
                $pipe->getResource()->getTransformer()->reverse(array_merge($request->getParsedBody()->toArray(), [
                    $this->model->getTable()->getPrimaryKey()->getName() => $pipe->getKey(),
                ]))
            )->save()
        );
    }

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function delete(Pipe $pipe): ResultRecord
    {
        $record = (new QueryResolver(
            $this->model,
            $pipe
        ))->byKey()->hydrate();

        $record->delete();

        return new ResultRecord($record);
    }
}
