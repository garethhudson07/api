<?php

namespace Api\Http\Requests;

use Oilstone\RsqlParser\Expression;
use Oilstone\RsqlParser\Parser as RsqlParser;

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

    protected $ancestry;

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
        $hierarchy = explode('.', $path);
        $name = array_pop($hierarchy);
        $ancestry = implode('.', $hierarchy);
        $instance = (new static($name));

        if ($ancestry) {
            $instance->setAncestry($ancestry);

            return static::parse($ancestry)->addRelation($instance);
        }

        return $instance;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setAncestry(string $path)
    {
        $this->ancestry = $path;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAncestry()
    {
        return $this->ancestry;
    }

    /**
     * @return mixed
     */
    public function path()
    {
        if ($this->ancestry) {
            return "{$this->ancestry}.{$this->name}";
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
