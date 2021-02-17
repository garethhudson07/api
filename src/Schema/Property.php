<?php

namespace Api\Schema;

use Api\Schema\Validation\Validator;
use Stitch\DBAL\Schema\Column;

/**
 * Class Property
 * @package Api\Schema
 * @method Property primary()
 * @method Property increments()
 * @method Property references(string $name)
 * @method Property on(string $table)
 * @method Property required()
 * @method Property minLength(int $minLength)
 * @method Property maxLength(int $minLength)
 * @method Property alpha()
 * @method Property alphaNumeric()
 * @method Property between($start, $end)
 * @method Property min($minimum)
 * @method Property max($maximum)
 * @method Property email()
 */
class Property
{
    /**
     * @const array
     */
    protected const PASSTHROUGH_MAP = [
        'validator' => [
            'required',
            'minLength',
            'maxLength',
            'alpha',
            'alphaNumeric',
            'between',
            'min',
            'max',
            'email',
        ],

        'column' => [
            'primary',
            'increments',
            'references',
            'on',
        ]
    ];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Column
     */
    protected $column;

    /**
     * @var Validator
     */
    protected $validator;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param $name
     * @param $arguments
     * @return self
     */
    public function __call($name, $arguments): self
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
