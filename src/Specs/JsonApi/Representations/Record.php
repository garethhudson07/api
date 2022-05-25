<?php


namespace Api\Specs\JsonApi\Representations;

use Api\Specs\Contracts\Representations\Record as RepresentationsRecord;
use Api\Support\Str;

class Record implements RepresentationsRecord
{
    /**
     * @var string
     */
    protected string $type;

    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @var array
     */
    protected array $relations = [];

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return static
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return array
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param string $name
     * @param mixed $relation
     * @return static
     */
    public function addRelation(string $name, mixed $relation): static
    {
        $this->relations[Str::camel($name)] = $relation;

        return $this;
    }
}
