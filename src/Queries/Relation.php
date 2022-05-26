<?php

namespace Api\Queries;

use Api\Resources\Resource as Resource;

/**
 * Class Relation
 * @package Api\Http\Requests
 */
class Relation
{
    protected string $name = '';

    protected $resource;

    protected $parent;

    protected string $path = '';

    protected array $fields = [];

    /**
     * @var Relations
     */
    protected Relations $relations;

    protected array $sort = [];

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
    public function setParent(Relation $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return string
     */
    public function path(): string
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
    public function addRelation(Relation $relation): static
    {
        $this->relations[] = $relation;

        return $this;
    }

    /**
     * @param string $path
     * @return Relation|null
     */
    public function pullRelation(string $path): ?Relation
    {
        return $this->relations->pull($path);
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
    public function setFields(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return Relations
     */
    public function getRelations(): Relations
    {
        return $this->relations;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param array $sort
     * @return $this
     */
    public function setSort(array $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @param Resource $resource
     * @return $this
     */
    public function setResource(Resource $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }
}
