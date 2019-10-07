<?php

namespace Api\Http\Requests;

use Closure;
use Oilstone\RsqlParser\Parser as RsqlParser;

class Bag
{
    protected $segments;

    protected $relations;

    protected $filters;

    protected $sort = [];

    protected $limit;

    protected $offset;

    public function __construct()
    {
        $this->relations = new Relations();
    }

    public static function parse(Raw $raw)
    {
        return (new static)->parseSegments($raw->segments())
            ->parseRelations($raw->relations() ?: '')
            ->parseFilters($raw->filters() ?: '')
            ->parseSort($raw->sort() ?: '')
            ->parseLimit($raw->limit() ?: '');
    }

    /**
     * @param string $input
     * @return $this
     */
    public function parseSegments(string $input)
    {
        $this->segments = array_values(array_filter(explode('/', $input)));

        return $this;
    }

    /**
     * @param $input
     * @return $this
     * @throws \Oilstone\RsqlParser\Exceptions\InvalidQueryStringException
     */
    public function parseFilters($input)
    {
        if (is_array($input)) {
            $resource = $this->resource();

            foreach ($input as $name => $filters) {
                if ($name === $resource) {
                    $this->filters = RsqlParser::parse($filters);
                    continue;
                }

                if ($relation = $this->relations->pull($name)) {
                    $relation->setFilters(RsqlParser::parse($filters));
                }
            }

            return $this;
        }

        $this->filters = RsqlParser::parse($input);

        return $this;
    }

    /**
     * @param string $input
     * @return $this
     */
    public function parseRelations(string $input)
    {
        foreach (array_filter(explode(',', $input)) as $item) {
            $this->relations[] = Relation::parse($item);
        }

        return $this;
    }

    /**
     * @param string $input
     * @return $this
     */
    public function parseSort(string $input)
    {
        foreach (array_filter(explode(',', $input)) as $item) {
            $this->sort = Order::parse($item);
        }

        return $this;
    }

    /**
     * @param $input
     * @return $this
     */
    public function parseLimit($input)
    {
        if (is_array($input)) {
            $resource = $this->resource();

            foreach ($input as $name => $limit) {
                if ($name === $resource) {
                    $this->limit = $limit;
                    continue;
                }

                if ($relation = $this->relations->pull($name)) {
                    $relation->setLimit($limit);
                }
            }

            return $this;
        }

        $this->limit = $input;

        return $this;
    }

    /**
     * @param array $segments
     * @return $this
     */
    public function setSegments(array $segments)
    {
        $this->segments = $segments;

        return $this;
    }

    /**
     * @return mixed
     */
    public function segments()
    {
        return $this->segments;
    }

    /**
     * @return mixed
     */
    public function resource()
    {
        if (!$this->segments) {
            return null;
        }

        return $this->segments[count($this->segments) - 1];
    }

    /**
     * @param array $relations
     * @return $this
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * @return mixed
     */
    public function relations()
    {
        return $this->relations;
    }

    /**
     * @param $filters
     * @return $this
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return mixed
     */
    public function filters()
    {
        return $this->filters;
    }

    /**
     * @param string $sort
     * @return $this
     */
    public function setSort(string $sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return mixed
     */
    public function sort()
    {
        return $this->sort;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return $this
     */
    public function limit()
    {
        return $this->limit;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return mixed
     */
    public function offset()
    {
        return $this->offset;
    }
}
