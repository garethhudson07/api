<?php

namespace Api\Queries;

use Oilstone\RsqlParser\Expression;

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
     * @var
     */
    protected $filters;

    /**
     * @var array
     */
    protected $relations;

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
     * @param string $path
     * @return Relation
     */
    public static function parse(string $path)
    {
        $pieces = explode('.', $path, 2);
        $name = array_shift($pieces);
        $instance = (new static($name));

        if ($pieces) {
            $instance->addRelation(
                static::parse($pieces[0])->setParent($instance)
            );
        }

        return $instance;
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
            $this->path = ($this->parent ? $this->parent->path() : '') . '.' . $this->name;
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
     * @return Expression
     */
    public function getFilters(): Expression
    {
        return $this->filters;
    }

    /**
     * @param Expression $filters
     * @return $this
     */
    public function setFilters(Expression $filters)
    {
        $this->filters = $filters;

        return $this;
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
}
