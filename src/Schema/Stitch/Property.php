<?php

namespace Api\Schema\Stitch;

use Api\Schema\Property as BaseProperty;
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
    protected $column;

    /**
     * Property constructor.
     * @param string $name
     * @param string $type
     * @param $validator
     * @param Column $column
     */
    public function __construct(string $name, string $type, $validator, Column $column)
    {
        parent::__construct($name, $type, $validator);

        $this->column = $column;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function column(string $name): self
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
     * @return self
     */
    public function __call($name, $arguments): self
    {
        parent::__call($name, $arguments);

        if (method_exists($this->column, $name)) {
            $this->column->{$name}(...$arguments);
        }

        return $this;
    }
}
