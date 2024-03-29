<?php

namespace Api\Repositories\Stitch\Bridge;

use Api\Queries\Paths\Path;
use Stitch\Queries\Query as BaseQuery;
use Api\Queries\Relations as RequestRelations;
use Api\Queries\Expression;
use Stitch\Result\Record as ResultRecord;
use Stitch\Result\Set;

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

    /**
     * @return BaseQuery
     */
    public function getBaseQuery(): BaseQuery
    {
        return $this->baseQuery;
    }

    /**
     * @return Set
     */
    public function get(): Set
    {
        return $this->baseQuery->get();
    }

    /**
     * @return null|ResultRecord
     */
    public function first(): ?ResultRecord
    {
        return $this->baseQuery->first();
    }

    /**
     * @param RequestRelations $relations
     * @return self
     */
    public function include(RequestRelations $relations): self
    {
        foreach ($relations->collapse() as $relation) {
            $path = $relation->getPath();
            $limit = $relation->getLimit();

            $this->baseQuery->with($path);

            $this->baseQuery->select(...array_map(function ($field) {
                $this->resolvePropertyPath($field->getPath());
            }, $relation->getFields()));

            foreach ($relation->getSort() as $order) {
                $this->baseQuery->orderBy(
                    $this->resolvePropertyPath($order->getPath()),
                    $order->getDirection()
                );
            }

            if ($limit !== NULL) {
                $this->baseQuery->limit($path, $limit);
            }
        }

        return $this;
    }

    /**
     * @param array $fields
     * @return self
     */
    public function select(array $fields): self
    {
        if ($fields) {
            $this->baseQuery->select(...array_map(function ($field) {
                return $this->resolvePropertyPath($field->getPath());
            }, $fields));
        }

        return $this;
    }

    /**
     * @param Expression $expression
     * @return self
     */
    public function where(Expression $expression): self
    {
        return $this->applyExpression($this->baseQuery, $expression);
    }

    /**
     * @param array $orders
     * @return self
     */
    public function orderBy(array $orders): self
    {
        foreach ($orders as $order) {
            $this->baseQuery->orderBy(
                $this->resolvePropertyPath($order->getPath()),
                $order->getDirection()
            );
        }

        return $this;
    }

    /**
     * @param $limit
     * @return self
     */
    public function limit($limit): self
    {
        if ($limit) {
            $this->baseQuery->limit($limit);
        }

        return $this;
    }

    /**
     * @param $offset
     * @return self
     */
    public function offset($offset): self
    {
        if ($offset) {
            $this->baseQuery->offset($offset);
        }

        return $this;
    }

    /**
     * @param $page
     * @return self
     */
    public function page($page, $limit): self
    {
        if ($page && $limit) {
            $this->baseQuery->limit($limit)->offset($page * $limit);
        }

        return $this;
    }

    /**
     * @param $search
     * @return self
     */
    public function search($search): self
    {
        if ($search) {
            // TODO: Implement full-text search in Stitch
            // $this->baseQuery->search($search);
        }

        return $this;
    }

    /**
     * @param $query
     * @param Expression $expression
     * @return self
     */
    protected function applyExpression($query, Expression $expression): self
    {
        foreach ($expression->getItems() as $item) {
            $method = $item['operator'] === 'OR' ? 'orWhere' : 'where';
            $constraint = $item['constraint'];

            if ($constraint instanceof Expression) {
                $query->{$method}(function ($query) use ($constraint) {
                    $this->applyExpression($query, $constraint);
                });
            } else {
                $operator = $constraint->getOperator();

                $query->{$method}(
                    $this->resolvePropertyPath($constraint->getPath()),
                    $this->resolveConstraintOperator($operator),
                    $this->resolveConstraintValue($operator, $constraint->getValue())
                );
            }
        }

        return $this;
    }

    /**
     * @param Path $path
     * @return string
     */
    protected function resolvePropertyPath(Path $path): string
    {
        $property = $path->getEntity();

        return implode(
            '.',
            array_filter([$path->prefix()->implode(), $property?->getColumn()?->getName() ?? $property->getPropertyName()])
        );
    }

    /**
     * @param $operator
     * @return mixed
     * @noinspection PhpMissingReturnTypeInspection
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
