<?php

namespace Api\Repositories\Stitch\Bridge;

use Oilstone\RsqlParser\Expression;
use Stitch\Queries\Query as BaseQuery;
use Api\Queries\Relations as RequestRelations;

class Query
{
    protected $baseQuery;

    protected const OPERATOR_MAP = [
        'IS NULL' => '=',
        'IS NOT NULL' => '!='
    ];

    protected const VALUE_MAP = [
        'IS NULL' => null,
        'IS NOT NULL' => null
    ];

    /**
     * Query constructor.
     * @param BaseQuery $baseQuery
     */
    public function __construct(BaseQuery $baseQuery)
    {
        $this->baseQuery = $baseQuery;
    }

    public function get()
    {
        return $this->baseQuery->get();
    }

    /**
     * @return null|\Stitch\Records\Record
     */
    public function first()
    {
        return $this->baseQuery->first();
    }

    /**
     * @param RequestRelations $relations
     * @return $this
     */
    public function with(RequestRelations $relations)
    {
        foreach ($relations as $relation) {
            $this->baseQuery->with($relation->path());

            $this->with($relation->getRelations());
        }

        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function select(array $fields)
    {
        if ($fields) {
            $this->baseQuery->select(...$fields);
        }

//        foreach ($relations->collapse() as $relation) {
//            if ($fields = $relation->getFields()) {
//                $path = $relation->path();
//
//                $this->baseQuery->limit(...array_map(function ($field) use ($path)
//                {
//                    return "$field.$path";
//                }, $fields));
//            }
//        }

        return $this;
    }

    /**
     * @param Expression $expression
     * @return Query
     */
    public function where(Expression $expression)
    {
        return $this->applyRsqlExpression($this->baseQuery, $expression);
    }

    /**
     * @param array $orders
     * @return $this
     */
    public function orderBy(array $orders)
    {
        foreach ($orders as $order) {
            $this->baseQuery->orderBy($order->getProperty(), $order->getDirection());
        }

        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        if ($limit) {
            $this->baseQuery->limit($limit);
        }

        return $this;
    }

    /**
     * @param $query
     * @param Expression $expression
     * @return $this
     */
    protected function applyRsqlExpression($query, Expression $expression)
    {
        foreach ($expression as $item) {
            $method = $item['operator'] === 'OR' ? 'orWhere' : 'where';
            $constraint = $item['constraint'];

            if ($constraint instanceof Expression) {
                $query->{$method}(function ($query) use ($constraint)
                {
                    $this->applyRsqlExpression($query, $constraint);
                });
            } else {
                $operator = $constraint->getOperator()->toSql();

                $query->{$method}(
                    $constraint->getColumn(),
                    $this->resolveConstraintOperator($operator),
                    $this->resolveConstraintValue($operator, $constraint->getValue())
                );
            }
        }

        return $this;
    }

    /**
     * @param $operator
     * @return mixed
     */
    protected function resolveConstraintOperator($operator)
    {
        if (array_key_exists($operator, $this::OPERATOR_MAP)) {
            $operator = $this::OPERATOR_MAP[$operator];
        }

        return $operator;
    }

    /**
     * @param $operator
     * @param $value
     * @return mixed
     */
    protected function resolveConstraintValue($operator, $value)
    {
        if (array_key_exists($operator, $this::VALUE_MAP)) {
            $value = $this::VALUE_MAP[$operator];
        }

        return $value;
    }
}