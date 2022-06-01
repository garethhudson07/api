<?php

namespace Api\Queries\Paths;

use Api\Queries\Relation;

class Path
{
    /**
     * @var Relation|null
     */
    protected ?Relation $relation = null;

    /**
     * @var mixed
     */
    protected $entity = null;

    /**
     * @return Relation|null
     */
    public function getRelation(): ?Relation
    {
        return $this->relation;
    }

    /**
     * @param Relation $relation
     * @return $this
     */
    public function setRelation(Relation $relation): static
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * @param mixed $entity
     * @return $this
     */
    public function setEntity(mixed $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntity(): mixed
    {
        return $this->entity;
    }


    /**
     * @return Pieces
     */
    public function prefix(): Pieces
    {
        return (new Pieces())->fill(
            $this->relation?->getPath()->resolve()->all() ?? []
        );
    }

    /**
     * @return Pieces
     */
    public function resolve(): Pieces
    {
        return (new Pieces)->fill(array_Merge(
            ...array_filter([
                $this->prefix()->all(),
                [$this->entity]
            ])
        ));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->resolve()->__toString();
    }
}
