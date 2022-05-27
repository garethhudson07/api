<?php

namespace Api\Specs\JsonApi;

use Api\Queries\Condition;
use Api\Queries\Expression;
use Api\Queries\Query;
use Api\Specs\Contracts\Parser as ParserContract;
use Api\Queries\Order;
use Api\Queries\Relation;
use Oilstone\RsqlParser\Exceptions\Exception as RsqlException;
use Oilstone\RsqlParser\Expression as RsqlExpression;
use Oilstone\RsqlParser\Parser as RsqlParser;
use Closure;

class Parser implements ParserContract
{
    protected Query $query;

    /**
     * @param Query $query
     * @return $this
     */
    public function setQuery(Query $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function parse($request, $config)
    {
        $params = $request->getQueryParams();

        $this->parseRelations($params[$config->get('relationsKey')] ?? '')
            ->parseFilters($params[$config->get('filtersKey')] ?? '')
            ->parseFields($params[$config->get('fieldsKey')] ?? '')
            ->parseSort($params[$config->get('sortKey')] ?? '')
            ->parseLimit($params[$config->get('limitKey')] ?? '')
            ->parseOffset($params[$config->get('offsetKey')] ?? '')
            ->parseSearch($params[$config->get('searchKey')] ?? '');
    }

    /**
     * @param array $items
     * @param Closure $callback
     * @return $this
     */
    protected function apply(array $items, Closure $callback): static
    {
        $type = $this->query->getType();
        $relations = $this->query->relations();

        foreach ($items as $name => $value) {
            if ($name === $type) {
                $callback($this->query, $value);

                foreach ($this->query->relations()->collapse() as $relation) {
                    if ($relation->getName() === $type) {
                        $callback($relation, $value);
                    }
                }

                continue;
            }

            if ($relation = $relations->get($name)) {
                $callback($relation, $value);
            }
        }

        return $this;
    }

    /**
     * @param string $input
     * @return array
     */
    public function list(string $input): array
    {
        return array_filter(explode(',', $input));
    }

    /**
     * @param string $input
     * @param int $limit
     * @return array
     */
    public function hierarchy(string $input, int $limit = PHP_INT_MAX): array
    {
        return explode('.', $input, $limit);
    }

    /**
     * @param string $input
     * @return $this
     */
    public function parseRelations(string $input): static
    {
        foreach ($this->list($input) as $item) {
            $this->query->addRelation($this->relation($item));
        }

        return $this;
    }

    /**
     * @param string $input
     * @return Relation
     */
    public function relation(string $input): Relation
    {
        $pieces = $this->hierarchy($input, 2);
        $name = array_shift($pieces);
        $instance = (new Relation($name));

        if ($pieces) {
            $instance->addRelation(
                $this->relation($pieces[0])->setParent($instance)
            );
        }

        return $instance;
    }

    /**
     * @param string $input
     * @return $this
     */
    public function parseFilters(string $input): static
    {
        try {
            $this->query->setFilters(
                $this->replaceRsqlExpression(
                    $this->query->filters(),
                    RsqlParser::parse($input)
                )
            );
        } catch (RsqlException $e) {}

        return $this;
    }

    /**
     * @param Expression $expression
     * @param RsqlExpression $rsqlExpression
     * @return Expression
     */
    protected function replaceRsqlExpression(Expression $expression, RsqlExpression $rsqlExpression): Expression
    {
        foreach ($rsqlExpression as $item) {
            $constraint = $item['constraint'];

            if ($constraint instanceof RsqlExpression) {
                $expression->add(
                    $item['operator'],
                    $this->replaceRsqlExpression(new Expression(), $constraint)
                );
            } else {
                $condition = new Condition();
                $pieces = $this->hierarchy($constraint->getColumn());
                $propertyName = array_pop($pieces);

                if (count($pieces)) {
                    $condition->setRelation(
                        $this->query->relations()->pull(implode('.', $pieces))
                    );
                }

                $condition->setPropertyName($propertyName)
                    ->setOperator($constraint->getOperator()->toSql())
                    ->setValue($constraint->getValue());

                $expression->add($item['operator'], $condition);
            }
        }

        return $expression;
    }

    /**
     * @param $input
     * @return $this
     */
    public function parseFields($input): static
    {
        if (is_array($input)) {
            $this->apply($input, function ($instance, $value)
            {
                $instance->setFields(
                    $this->list($value)
                );
            });

            return $this;
        }

        $this->query->setFields(
            $this->list($input)
        );

        return $this;
    }

    /**
     * @param $input
     * @return $this
     */
    public function parseSort($input): static
    {
        if (is_array($input)) {
            $this->apply($input, function ($instance, $value)
            {
                $instance->setSort(
                    $this->sort($value)
                );
            });

            return $this;
        }

        $this->query->setSort(
            $this->sort($input)
        );

        return $this;
    }

    /**
     * @param string $input
     * @return array
     */
    public function sort(string $input): array
    {
        $sort = [];

        foreach ($this->list($input) as $item) {
            $sort[] = $this->order($item);
        }

        return $sort;
    }

    /**
     * @param string $input
     * @return Order
     */
    public function order(string $input): Order
    {
        $direction = 'ASC';
        $operator = substr($input, 0, 1);

        if ($operator == '-' || $operator == '+') {
            if ($operator == '-') {
                $direction = 'DESC';
            }

            $input = substr($input, 1);
        }

        return (new Order())->setProperty($input)->setDirection($direction);
    }

    /**
     * @param $input
     * @return $this
     */
    public function parseLimit($input): static
    {
        if (is_array($input)) {
            $this->apply($input, function ($instance, $value)
            {
                $instance->setLimit(
                    intval($value)
                );
            });

            return $this;
        }

        $this->query->setLimit(
            intval($input)
        );

        return $this;
    }

    /**
     * @param $input
     * @return $this
     */
    public function parseOffset($input): static
    {
        $this->query->setOffset(
            intval($input)
        );

        return $this;
    }

    /**
     * @param $input
     * @return $this
     */
    public function parseSearch($input): static
    {
        $this->query->setSearch((string) $input);

        return $this;
    }

    /**
     * @param array $input
     * @return array
     */
    public function attributes(array $input): array
    {
        if (array_key_exists('data', $input) && array_key_exists('attributes', $input['data'])) {
            return $input['data']['attributes'];
        }

        return [];
    }
}
