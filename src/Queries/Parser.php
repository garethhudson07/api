<?php

namespace Api\Queries;

use Oilstone\RsqlParser\Parser as RsqlParser;

class Parser
{
    /**
     * @param string $input
     * @return array
     */
    public static function list(string $input)
    {
        return array_filter(explode(',', $input));
    }

    /**
     * @param string $input
     * @return array
     */
    public static function hierarchy(string $input)
    {
        return explode('.', $input);
    }

    /**
     * @param string $input
     * @return int
     */
    public static function integer(string $input)
    {
        return intval($input);
    }

    /**
     * @param string $input
     * @return array
     */
    public static function relations(string $input)
    {
        $relations = [];

        foreach (static::list($input) as $item) {
            $relations[] = Relation::parse($item);
        }

        return $relations;
    }

    /**
     * @param string $input
     * @return \Oilstone\RsqlParser\Expression
     * @throws \Oilstone\RsqlParser\Exceptions\InvalidQueryStringException
     */
    public static function filters(string $input)
    {
        return RsqlParser::parse($input);
    }

    /**
     * @param string $input
     * @return array
     */
    public static function sort(string $input)
    {
        $sort = [];

        foreach (Parser::list($input) as $item) {
            $sort[] = Order::parse($item);
        }

        return $sort;
    }
}
