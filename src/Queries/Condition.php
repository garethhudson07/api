<?php

namespace Api\Queries;

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

    /**
     * @var string
     */
    protected string $property = '';

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
     * @param string $property
     * @return $this
     */
    public function setProperty(string $property): static
    {
        $this->property = $property;

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
            array_filter([$this->relation?->path(), $this->property]),
        );
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
