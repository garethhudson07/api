<?php

namespace Api\Schema\Stitch;

use Api\Schema\Property as BaseProperty;
use Api\Schema\Validation\Nested as NestedValidator;
use Api\Schema\Validation\Validator;
use Stitch\DBAL\Schema\Column;

/**
 * Class Property
 * @package Api\Schema
 */
class Property extends BaseProperty
{
    /**
     * @var Column
     */
    protected Column $column;

    /**
     * Property constructor.
     * @param string $name
     * @param string $type
     * @param $validator
     * @param Column $column
     */
    public function __construct(string $name, string $type, Validator|NestedValidator $validator, Column $column)
    {
        parent::__construct($name, $type, $validator);

        $this->column = $column;
    }

    /**
     * @param string $name
     * @return static
     */
    public function column(string $name): static
    {
        $this->column->rename($name);

        return $this;
    }

    /**
     * @return Column
     */
    public function getColumn(): Column
    {
        return $this->column;
    }

    /**
     * @param $name
     * @param $arguments
     * @return static
     */
    public function __call($name, $arguments): static
    {
        parent::__call($name, $arguments);

        if (method_exists($this->column, $name)) {
            $this->column->{$name}(...$arguments);
        }

        return $this;
    }
}
