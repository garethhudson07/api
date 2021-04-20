<?php

namespace Api\Schema\Stitch;

use Api\Schema\Property as BaseProperty;
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
    protected $column;

    /**
     * Property constructor.
     * @param string $name
     * @param string $type
     * @param Validator $validator
     * @param Column $column
     */
    public function __construct(string $name, string $type, Validator $validator, Column $column)
    {
        parent::__construct($name, $type, $validator);

        $this->column = $column;
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
