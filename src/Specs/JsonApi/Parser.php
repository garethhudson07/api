<?php

namespace Api\Specs\JsonApi;

use Api\Exceptions\ApiException;
use Api\Queries\Condition;
use Api\Queries\Expression;
use Api\Queries\Field;
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
        $relations = $this->query->getRelations();

        foreach ($items as $name => $value) {
            if ($name === $type) {
                $callback($this->query, $value);

                foreach ($this->query->getRelations()->collapse() as $relation) {
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
    protected function list(string $input): array
    {
        return array_filter(preg_split('/\,(?![^(]*\))/', $input));
    }

    /**
     * @param string $input
     * @param int $limit
     * @return array
     */
    protected function hierarchy(string $input, int $limit = PHP_INT_MAX): array
    {
        return explode('.', $input, $limit);
    }

    /**
     * @param string $input
     * @return $this
     */
    protected function parseRelations(string $input): static
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
    protected function relation(string $input): Relation
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
    protected function parseFilters(string $input): static
    {
        try {
            $this->query->setFilters(
                $this->replaceRsqlExpression(
                    $this->query->getFilters(),
                    RsqlParser::parse($input)
                )
            );
        } catch (RsqlException $e) {
            throw new ApiException($e->getMessage(), $e->getCode(), $e);
        }

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

                if (count($pieces) && $relation = $this->query->getRelations()->pull(implode('.', $pieces))) {
                    $condition->setRelation($relation);
                } else {
                    $propertyName = $constraint->getColumn();
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
    protected function parseFields($input): static
    {
        if (is_array($input)) {
            $this->apply($input, function ($instance, $value) {
                $this->fillFields($instance, $value);
            });

            return $this;
        }

        $this->fillFields($this->query, $input);

        return $this;
    }

    /**
     * @param $instance
     * @param string $input
     * @return static
     */
    protected function fillFields($instance, string $input): static
    {
        foreach ($this->list($input) as $item) {
            $instance->addField(
                (new Field())->setPropertyName($item)
            );
        }

        return $this;
    }

    /**
     * @param $input
     * @return $this
     */
    protected function parseSort($input): static
    {
        if (is_array($input)) {
            $this->apply($input, function ($instance, $value)
            {
                $this->fillOrders($instance, $value);
            });

            return $this;
        }

        $this->fillOrders($this->query, $input);

        return $this;
    }

    /**
     * @param $instance
     * @param string $input
     * @return $this
     */
    protected function fillOrders($instance, string $input): static
    {
        foreach ($this->list($input) as $item) {
            $instance->addOrder($this->order($item));
        }

        return $this;
    }

    /**
     * @param string $input
     * @return Order
     */
    protected function order(string $input): Order
    {
        $direction = 'ASC';
        $operator = substr($input, 0, 1);

        if ($operator == '-' || $operator == '+') {
            if ($operator == '-') {
                $direction = 'DESC';
            }

            $input = substr($input, 1);
        }

        return (new Order())->setPropertyName($input)->setDirection($direction);
    }

    /**
     * @param $input
     * @return $this
     */
    protected function parseLimit($input): static
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
    protected function parseOffset($input): static
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
    protected function parseSearch($input): static
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
        return $input['data']['attributes'] ?? [];
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function id(array $input): mixed
    {
        return $input['data']['id'] ?? null;
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function type(array $input): mixed
    {
        return $input['data']['type'] ?? null;
    }
}
