<?php

namespace Api\Repositories\Stitch\Bridge;

use Psr\Http\Message\ServerRequestInterface;
use Stitch\Model;
use Api\Pipeline\Pipe;

class QueryResolver
{
    protected $model;

    protected $pipe;

    public function __construct(Model $model, Pipe $pipe)
    {
        $this->model = $model;
        $this->pipe = $pipe;
    }

    public function byKey()
    {
        return $this->keyedQuery()->first();
    }

    public function record(ServerRequestInterface $request)
    {
        return Query::resolve(
            $this->keyedQuery(),
            $request
        )->first();
    }

    public function collection(ServerRequestInterface $request)
    {
        return Query::resolve(
            $this->baseQuery(),
            $request
        )->get();
    }

    protected function keyedQuery()
    {
        return $this->baseQuery()->where(
            $this->model->getTable()->getPrimaryKey()->getName(),
            $this->pipe->getKey()
        );
    }

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
