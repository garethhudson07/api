<?php

namespace Api\Http\Requests;

use Closure;

class Bag
{
    protected $segments;

    protected $relations;

    protected $fields = [];

    protected $filters;

    protected $sort = [];

    protected $limit;

    protected $offset;

    /**
     * Bag constructor.
     */
    public function __construct()
    {
        $this->relations = new Relations();
    }

    /**
     * @param Raw $raw
     * @return Bag
     */
    public static function parse(Raw $raw)
    {
        return (new static)->parseSegments($raw->segments())
            ->parseRelations($raw->relations() ?: '')
            ->parseFields($raw->fields() ?: '')
            ->parseFilters($raw->filters() ?: '')
            ->parseSort($raw->sort() ?: '')
            ->parseLimit($raw->limit() ?: '');
    }

    /**
     * @param array $items
     * @param Closure $callback
     * @return $this
     */
    protected function apply(array $items, Closure $callback)
    {
        $resource = $this->resource();

        foreach ($items as $name => $value) {
            if ($name === $resource) {
                $callback($this, $value);
                continue;
            }

            if ($relation = $this->relations->pull($name)) {
                $callback($relation, $value);
            }
        }

        return $this;
    }

    /**
     * @param string $input
     * @return $this
     */
    public function parseSegments(string $input)
    {
        $this->segments = Parser::segments($input);

        return $this;
    }

    /**
     * @param $input
     * @return $this
     */
    public function parseFields($input)
    {
        if (is_array($input)) {
            $this->apply($input, function ($instance, $value)
            {
                $instance->setFields(Parser::list($value));
            });

            return $this;
        }

        return $this->setFields(Parser::list($input));
    }

    /**
     * @param $input
     * @return $this|Bag
     * @throws \Oilstone\RsqlParser\Exceptions\InvalidQueryStringException
     */
    public function parseFilters($input)
    {
        if (is_array($input)) {
            $this->apply($input, function ($instance, $value)
            {
                $instance->setFilters(Parser::filters($value));
            });

            return $this;
        }

        return $this->setFilters(Parser::filters($input));
    }

    /**
     * @param string $input
     * @return $this
     */
    public function parseRelations(string $input)
    {
        $this->relations = Parser::relations($input);

        return $this;
    }

    /**
     * @param string $input
     * @return $this
     */
    public function parseSort(string $input)
    {
        $this->sort = Parser::sort($input);

        return $this;
    }

    /**
     * @param $input
     * @return $this
     */
    public function parseLimit($input)
    {
        if (is_array($input)) {
            $this->apply($input, function ($instance, $value)
            {
                $instance->setLimit(Parser::integer($value));
            });

            return $this;
        }

        return $this->setLimit(Parser::integer($input));
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
     * @param array $fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return array
     */
    public function fields(): array
    {
        return $this->fields;
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
