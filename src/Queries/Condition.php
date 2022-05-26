<?php

namespace Api\Queries;

use Api\Schema\Property;

/**
 * Class Condition
 * @package Stitch\DBAL\Builders
 */
class Condition
{
    /**
     * @var Relation
     */
    protected Relation $relation;

    protected $property;

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
     * @param Relation $relation
     * @return $this
     */
    public function setRelation(Relation $relation): static
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * @param Property $property
     * @return $this
     */
    public function setProperty(Property $property): static
    {
        $this->property = $property;

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
     * @return Relation|null
     */
    public function getRelation(): ?Relation
    {
        return $this->relation;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return implode(
            '.',
            array_filter([$this->getPathPrefix(), $this->propertyName]),
        );
    }

    /**
     * @return string
     */
    public function getPathPrefix(): string
    {
        return $this->relation?->path() ?? '';
    }

    /**
     * @return mixed
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return mixed|string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
