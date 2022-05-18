<?php

namespace Api\Queries;

use Api\Config\Manager;
use Closure;
use Api\Specs\Contracts\Parser as ParserInterface;
use Psr\Http\Message\ServerRequestInterface;

class Query
{
    protected $parser;

    protected $type;

    protected $relations;

    protected $fields = [];

    protected $filters;

    protected $sort = [];

    protected $limit;

    protected $offset;

    protected $search;

    /**
     * Query constructor.
     * @param ParserInterface $parser
     * @param string $type
     */
    public function __construct(ParserInterface $parser, string $type)
    {
        $this->parser = $parser;
        $this->type = $type;
    }

    /**
     * @param ParserInterface $parser
     * @param ServerRequestInterface $request
     * @param Manager $config
     * @return Query
     */
    public static function extract(ParserInterface $parser, ServerRequestInterface $request, Manager $config)
    {
        $segments = $request->getAttribute('segments') ?? [];
        $params = $request->getQueryParams();

        return (new static(
            $parser,
            $segments[count($segments) - 1] ?? ''
        ))->parseRelations($params[$config->get('relationsKey')] ?? '')
            ->parseFields($params[$config->get('fieldsKey')] ?? '')
            ->parseFilters($params[$config->get('filtersKey')] ?? '')
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
                $instance->setFields(
                    $this->parser->fields($value)
                );
            });

            return $this;
        }

        return $this->setFields(
            $this->parser->fields($input)
        );
    }

    /**
     * @param $input
     * @return $this|Query
     * @throws \Oilstone\RsqlParser\Exceptions\InvalidQueryStringException
     */
    public function parseFilters($input)
    {
        return $this->setFilters(
            $this->parser->filters($input)
        );
    }

    /**
     * @param string $input
     * @return $this
     */
    public function parseRelations(string $input)
    {
        $this->relations = $this->parser->relations($input);

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
                $instance->setSort(
                    $this->parser->sort($value)
                );
            });

            return $this;
        }

        return $this->setSort(
            $this->parser->sort($input)
        );
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
                $instance->setLimit(
                    intval($value)
                );
            });

            return $this;
        }

        return $this->setLimit(
            intval($input)
        );
    }

    /**
     * @param $input
     * @return $this
     */
    public function parseOffset($input)
    {
        $this->setOffset(
            intval($input)
        );

        return $this;
    }

    /**
     * @param $input
     * @return $this
     */
    public function parseSearch($input)
    {
        $this->setSearch((string) $input);

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

    /**
     * @param $search
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @return mixed
     */
    public function search()
    {
        return $this->search;
    }
}
