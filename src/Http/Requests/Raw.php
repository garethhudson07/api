<?php

namespace Api\Http\Requests;

use Api\Config\Manager;
use Psr\Http\Message\ServerRequestInterface;

class Raw
{
    protected $segments;

    protected $relations;

    protected $filters;

    protected $sort;

    protected $limit;

    protected $offset;

    public static function extract(ServerRequestInterface $request, Manager $config)
    {
        $params = $request->getQueryParams();

        return (new static)->setSegments($request->getUri()->getPath())
            ->setRelations($params[$config->get('relationsKey')] ?? null)
            ->setFilters($params[$config->get('filtersKey')] ?? null)
            ->setSort($params[$config->get('sortKey')] ?? null)
            ->setLimit($params[$config->get('limitKey')] ?? null)
            ->setOffset($params[$config->get('offsetKey')] ?? null);
    }

    /**
     * @param string $segments
     * @return $this
     */
    public function setSegments(string $segments)
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
     * @param string $relations
     * @return $this
     */
    public function setRelations($relations)
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
    public function setSort($sort)
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
