<?php

namespace Api\Specs\JsonApi;

use Api\Specs\Contracts\Parser as ParserContract;
use Api\Queries\Order;
use Api\Queries\Relation;
use Api\Queries\Relations;
use Api\Support\Str;
use Oilstone\RsqlParser\Expression;
use Oilstone\RsqlParser\Parser as RsqlParser;

class Parser implements ParserContract
{
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
        return array_map([Str::class, 'snake'], $this->list($input));
    }

    /**
     * @param string $input
     * @return Relations
     */
    public function relations(string $input): Relations
    {
        $relations = new Relations();

        foreach ($this->list($input) as $item) {
            $relations->push($this->relation($item));
        }

        return $relations;
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
     * @return \Oilstone\RsqlParser\Expression
     * @throws \Oilstone\RsqlParser\Exceptions\InvalidQueryStringException
     */
    public function filters(string $input)
    {
        return $this->transformFilterColumns(
            RsqlParser::parse($input)
        );
    }

    /**
     * @param Expression $expression
     * @return Expression
     */
    protected function transformFilterColumns(Expression $expression)
    {
        foreach ($expression as $item) {
            $constraint = $item['constraint'];

            if ($constraint instanceof Expression) {
                $this->transformFilterColumns($constraint);
            } else {
                $hierarchy = $this->hierarchy($constraint->getColumn());
                $hierarchy[] = Str::snake(array_pop($hierarchy));

                $constraint->setColumn(
                    implode('.', $hierarchy)
                );
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

        foreach (Parser::list($input) as $item) {
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

        return (new Order())->setProperty(
            Str::snake($input)
        )->setDirection($direction);
    }
}
