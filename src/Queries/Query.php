<?php

namespace Api\Queries;

use Api\Config\Manager;
use Closure;
use Psr\Http\Message\ServerRequestInterface;

class Query
{
    protected $type;

    protected $relations;

    protected $fields = [];

    protected $filters;

    protected $sort = [];

    protected $limit;

    protected $offset;

    /**
     * Query constructor.
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
        $this->relations = new Relations();
    }

    /**
     * @param ServerRequestInterface $request
     * @param Manager $config
     * @return Query
     */
    public static function extract(ServerRequestInterface $request, Manager $config)
    {
        $segments = $request->getAttribute('segments');
        $params = $request->getQueryParams();

        return (new static(
            $segments[count($segments) - 1]
        ))->parseRelations($params[$config->get('relationsKey')] ?? '')
            ->parseFields($params[$config->get('fieldsKey')] ?? '')
            ->parseFilters($params[$config->get('filtersKey')] ?? '')
            ->parseSort($params[$config->get('sortKey')] ?? '')
            ->parseLimit($params[$config->get('limitKey')] ?? '')
            ->parseOffset($params[$config->get('offsetKey')] ?? '');
    }

    /**
     * @param array $items
     * @param Closure $callback
     * @return $this
     */
    protected function apply(array $items, Closure $callback)
    {
        foreach ($items as $name => $value) {
            if ($name === $this->type) {
                $callback($this, $value);

                foreach ($this->relations->collapse() as $relation) {
                    if ($relation->getName() === $this->type) {
                        $callback($relation, $value);
                    }
                }

                continue;
            }

            if ($relation = $this->relations->pull($name)) {
                $callback($relation, $value);
            }
        }

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
     * @return $this|Query
     * @throws \Oilstone\RsqlParser\Exceptions\InvalidQueryStringException
     */
    public function parseFilters($input)
    {
        return $this->setFilters(Parser::filters($input));
    }

    /**
     * @param string $input
     * @return $this
     */
    public function parseRelations(string $input)
    {
        $this->relations->fill(Parser::relations($input));

        return $this;
    }

    /**
     * @param $input
     * @return $this
     */
    public function parseSort($input)
    {
        if (is_array($input)) {
            $this->apply($input, function ($instance, $value)
            {
                $instance->setSort(Parser::sort($value));
            });

            return $this;
        }
        
        return $this->setSort(Parser::sort($input));
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
     * @param $input
     * @return $this
     */
    public function parseOffset($input)
    {
        $this->setOffset(Parser::integer($input));

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
     * @param array $sort
     * @return $this
     */
    public function setSort(array $sort)
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
