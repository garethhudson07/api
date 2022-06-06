<?php

namespace Api\Queries;

use Api\Resources\Relations\Relation as ResourceRelation;
use Api\Queries\Paths\Path;

/**
 * Class Relation
 * @package Api\Http\Requests
 */
class Relation
{
    protected Path $path;

    protected string $name = '';

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
        $this->path = new Path();
        $this->relations = new Relations();
    }

    /**
     * @param Relation $parent
     * @return $this
     */
    public function setParent(Relation $parent): static
    {
        $this->path->setRelation($parent);

        return $this;
    }

    /**
     * @return Path
     */
    public function getPath(): Path
    {
        return $this->path;
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
     * @param Field $field
     * @return $this
     */
    public function addField(Field $field): static
    {
        $this->fields[] = $field;

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
     * @param Order $order
     * @return $this
     */
    public function addOrder(Order $order): static
    {
        $this->sort[] = $order;

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
     * @param ResourceRelation $resource
     * @return $this
     */
    public function setResource(ResourceRelation $resource): static
    {
        $this->path->setEntity($resource);

        return $this;
    }
}
