<?php

namespace Api\Specs\JsonApi;

use Api\Queries\Condition;
use Api\Queries\Expression;
use Api\Specs\Contracts\Parser as ParserContract;
use Api\Queries\Order;
use Api\Queries\Relation;
use Api\Queries\Relations;
use Oilstone\RsqlParser\Exceptions\Exception as RsqlException;
use Oilstone\RsqlParser\Expression as RsqlExpression;
use Oilstone\RsqlParser\Parser as RsqlParser;

class Parser implements ParserContract
{

    protected $relations;

    protected $filters;

    public function parse($request, $config)
    {
        $params = $request->getQueryParams();

        $this->parseRelations($params[$config->get('relationsKey')] ?? '');
        $this->parseFilters($params[$config->get('filtersKey')] ?? '');

        var_dump($this->filters->getItems()[0]);
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
     * @return array
     */
    public function hierarchy(string $input): array
    {
        return explode('.', $input);
    }

    /**
     * @param string $input
     * @return array
     */
    public function fields(string $input): array
    {
        return $this->list($input);
    }

    /**
     * @param string $input
     * @return void
     */
    public function parseRelations(string $input): void
    {
        $this->relations = new Relations();

        foreach ($this->list($input) as $item) {
            $this->relations->push($this->relation($item));
        }
    }

    /**
     * @param string $input
     * @return Relation
     */
    public function relation(string $input): Relation
    {
        $pieces = explode('.', $input, 2);
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
     * @return void
     */
    public function parseFilters(string $input): void
    {
        try {
            $this->filters = $this->replaceRsqlExpression(
                RsqlParser::parse($input)
            );
        } catch (RsqlException $e) {}
    }

    /**
     * @param RsqlExpression $rsqlExpression
     * @return Expression
     */
    protected function replaceRsqlExpression(RsqlExpression $rsqlExpression): Expression
    {
        $expression = new Expression();

        foreach ($rsqlExpression as $item) {
            $constraint = $item['constraint'];

            if ($constraint instanceof RsqlExpression) {
                $expression->add(
                    $item['operator'],
                    $this->replaceRsqlExpression($constraint)
                );
            } else {
                $condition = new Condition();
                $path = $constraint->getColumn();
                $pieces = explode('.', $path, 2);

                if (count($pieces) > 1) {
                    $condition->setRelation(
                        $this->relations->pull(array_shift($pieces))
                    );
                }

                $condition->setProperty($pieces[0])
                    ->setOperator($constraint->getOperator()->toSql())
                    ->setValue($constraint->getValue());

                $expression->add($item['operator'], $condition);
            }
        }

        return $expression;
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
     * @param array $input
     * @return array
     */
    public function attributes(array $input): array
    {
        $output = [];

        foreach ($input as $key => $value) {
            if (!is_integer($key)) {
                $key = Str::snake($key);
            }

            if (is_array($value)) {
                $output[$key] = $this->attributes($value);
                continue;
            }

            $output[$key] = $value;
        }

        return $output;
    }
}
