<?php


namespace Api\Specs\JsonApi\Representations;


class Record
{
    protected string $type;

    protected array $attributes = [];

    protected $relations = [];

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    public function addRelation(string $name, $relation)
    {
        $this->relations[$name] = $relation;

        return $this;
    }
}
