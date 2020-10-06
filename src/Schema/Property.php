<?php

namespace Api\Schema;

use Api\Schema\Validation\Validator;
use Stitch\DBAL\Schema\Column;

/**
 * Class Property
 * @package Api\Schema
 * @method Property primary()
 * @method Property references(string $columnName)
 * @method Property on(string $tableName)
 */
class Property
{
    protected $name;

    protected $type;

    protected $column;

    protected $validator;

    protected const PASSTHROUGH_MAP = [
        'validator' => [
            'required'
        ],
        'column' => [
            'primary',
            'increments',
            'references',
            'on'
        ]
    ];

    /**
     * Property constructor.
     * @param string $name
     * @param string $type
     * @param Column $column
     * @param Validator $validator
     */
    public function __construct(string $name, string $type, Column $column, Validator $validator)
    {
        $this->name = $name;
        $this->type = $type;
        $this->column = $column;
        $this->validator = $validator;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (in_array($name, $this::PASSTHROUGH_MAP['column'])) {
            $this->column->{$name}(...$arguments);
        }

        if (in_array($name, $this::PASSTHROUGH_MAP['validator']) || $this->validator->hasRule($name)) {
            $this->validator->{$name}(...$arguments);
        }

        return $this;
    }
}
