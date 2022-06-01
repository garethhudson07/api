<?php

namespace Api\Queries;

use Api\Schema\Property;
use Api\Queries\Paths\Path;

/**
 * Class Condition
 * @package Stitch\DBAL\Builders
 */
class Condition
{
    protected Path $path;

    /**
     * @var string
     */
    protected string $propertyName = '';

    /**
     * @var string
     */
    protected string $operator = '=';

    /**
     * @var mixed
     */
    protected $value;

    /**
     * Condition constructor.
     */
    public function __construct()
    {
        $this->path = new Path();
    }

    /**
     * @param Relation $relation
     * @return $this
     */
    public function setRelation(Relation $relation): static
    {
        $this->path->setRelation($relation);

        return $this;
    }

    /**
     * @param Property $property
     * @return $this
     */
    public function setProperty(Property $property): static
    {
        $this->path->setEntity($property);

        return $this;
    }

    /**
     * @param string $propertyName
     * @return $this
     */
    public function setPropertyName(string $propertyName): static
    {
        $this->propertyName = $propertyName;

        return $this;
    }

    /**
     * @param string $operator
     * @return $this
     */
    public function setOperator(string $operator): static
    {
        $this->operator = strtoupper($operator);

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value): static
    {
        $this->value = $value;

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
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
