<?php

namespace Api\Repositories\Stitch\Bridge;

use Psr\Http\Message\ServerRequestInterface;
use Stitch\Model;
use Api\Pipeline\Pipes\Pipe;
use Stitch\Queries\Query as BaseQuery;
use Stitch\Result\Record as ResultRecord;
use Stitch\Result\Set;

/**
 * Class QueryResolver
 * @package Api\Repositories\Stitch\Bridge
 */
class QueryResolver
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Pipe
     */
    protected $pipe;

    /**
     * QueryResolver constructor.
     * @param Model $model
     * @param Pipe $pipe
     */
    public function __construct(Model $model, Pipe $pipe)
    {
        $this->model = $model;
        $this->pipe = $pipe;
    }

    /**
     * @return null|ResultRecord
     */
    public function byKey(): ?ResultRecord
    {
        return $this->keyedQuery()->first();
    }

    /**
     * @param ServerRequestInterface $request
     * @return null|ResultRecord
     */
    public function record(ServerRequestInterface $request): ?ResultRecord
    {
        return $this->resolve($this->keyedQuery(), $request)->first();
    }

    /**
     * @param ServerRequestInterface $request
     * @return Set
     */
    public function collection(ServerRequestInterface $request): Set
    {
        return $this->resolve($this->baseQuery(), $request)->get();
    }

    /**
     * @param BaseQuery $baseQuery
     * @param ServerRequestInterface $request
     * @return Query
     */
    public function resolve(BaseQuery $baseQuery, ServerRequestInterface $request): Query
    {
        $parsedQuery = $request->getAttribute('parsedQuery');

        return (new Query($baseQuery))->include($parsedQuery->relations())
            ->select($parsedQuery->getFields())
            ->where($parsedQuery->getFilters())
            ->orderBy($parsedQuery->getSort())
            ->limit($parsedQuery->getLimit())
            ->offset($parsedQuery->getOffset())
            ->search($parsedQuery->getSearch());
    }

    /**
     * @return BaseQuery
     */
    public function keyedQuery(): BaseQuery
    {
        return $this->baseQuery()->where(
            $this->model->getTable()->getPrimaryKey()->getName(),
            $this->pipe->getKey()
        );
    }

    /**
     * @return BaseQuery
     */
    public function baseQuery(): BaseQuery
    {
        $baseQuery = $this->model->query()->dehydrated();

        if ($this->pipe->isScoped()) {
            $scope = $this->pipe->getScope();

            $baseQuery->where($scope->getKey(), $scope->getValue());
        }

        return $baseQuery;
    }
}
