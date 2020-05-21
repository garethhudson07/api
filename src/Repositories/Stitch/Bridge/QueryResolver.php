<?php

namespace Api\Repositories\Stitch\Bridge;

use Psr\Http\Message\ServerRequestInterface;
use Stitch\Model;
use Api\Pipeline\Pipes\Pipe;
use Stitch\Queries\Query as BaseQuery;

class QueryResolver
{
    protected $model;

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
     * @return null|\Stitch\Records\Record
     */
    public function byKey()
    {
        return $this->keyedQuery()->first();
    }

    /**
     * @param ServerRequestInterface $request
     * @return null|\Stitch\Records\Record
     */
    public function record(ServerRequestInterface $request)
    {
        return $this->resolve($this->keyedQuery(), $request)->first();
    }

    public function collection(ServerRequestInterface $request)
    {
        return $this->resolve($this->baseQuery(), $request)->get();
    }

    /**
     * @param BaseQuery $baseQuery
     * @param ServerRequestInterface $request
     * @return Query
     */
    public function resolve(BaseQuery $baseQuery, ServerRequestInterface $request)
    {
        $parsedQuery = $request->getAttribute('query');

        return (new Query($baseQuery))->with($parsedQuery->relations())
            ->select($parsedQuery->fields())
            ->where($parsedQuery->filters())
            ->orderBy($parsedQuery->sort())
            ->limit($parsedQuery->limit());
    }

    /**
     * @return BaseQuery
     */
    protected function keyedQuery()
    {
        return $this->baseQuery()->where(
            $this->model->getTable()->getPrimaryKey()->getName(),
            $this->pipe->getKey()
        );
    }

    /**
     * @return BaseQuery
     */
    protected function baseQuery()
    {
        $baseQuery = $this->model->query()->dehydrated();

        if ($this->pipe->isScoped()) {
            $scope = $this->pipe->getScope();

            $baseQuery->where($scope->getKey(), $scope->getValue());
        }

        return $baseQuery;
    }
}
