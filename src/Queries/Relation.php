<?php

namespace Api\Queries;

/**
 * Class Relation
 * @package Api\Http\Requests
 */
class Relation
{
    /**
     * @var
     */
    protected $name;

    protected $parent;

    protected $path;

    protected $fields = [];

    /**
     * @var array
     */
    protected $relations;

    protected $sort = [];

    protected $limit;

    /**
     * Relation constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->relations = new Relations();
    }

    /**
     * @param Relation $parent
     * @return $this
     */
    public function setParent(Relation $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function path()
    {
        if ($this->path) {
            return $this->path;
        }

        if($this->parent) {
            $this->path = "{$this->parent->path()}.{$this->name}";

            return $this->path;
        }

        return $this->name;
    }

    /**
     * @param Relation $relation
     * @return $this
     */
    public function addRelation(Relation $relation)
    {
        $this->relations[] = $relation;

        return $this;
    }

    /**
     * @param string $name
     * @return null
     */
    public function pullRelation(string $name)
    {
        return $this->relations->pull($name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
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
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param array $relations
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;
    }

    /**
     * @return bool
     */
    public function hasRelations()
    {
        return $this->relations->count() > 0;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
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
    public function getSort()
    {
        return $this->sort;
    }
}
