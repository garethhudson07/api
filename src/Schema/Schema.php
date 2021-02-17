<?php

namespace Api\Schema;

use Api\Schema\Validation\Aggregate as Validators;
use Api\Schema\Validation\ValidationException;
use Api\Schema\Validation\Validator;
use Stitch\DBAL\Schema\Column;
use Stitch\DBAL\Schema\Table;

/**
 * Class Schema
 * @package Api\Schema
 * @method Property string(string $name)
 * @method Property integer(string $name)
 * @method Property boolean(string $name)
 * @method Property primary()
 * @method Property increments()
 * @method Property references(string $name)
 * @method Property on(string $table)
 * @method Property required()
 */
class Schema
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var Validators
     */
    protected $validators;

    /**
     * Schema constructor.
     */
    public function __construct()
    {
        $this->table = new Table();
        $this->validators = new Validators();
    }

    /**
     * @param string $name
     * @return self
     */
    public function table(string $name): self
    {
        $this->table->name($name);

        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    public function database(string $name): self
    {
        $this->table->database($name);

        return $this;
    }

    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * @param array $input
     * @return bool
     * @throws ValidationException
     */
    public function validate(array $input): bool
    {
        return $this->validators->run($input);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return Property
     */
    public function __call(string $method, array $arguments): Property
    {
        return $this->addProperty($method, $arguments[0]);
    }

    /**
     * @param $type
     * @param $name
     * @return Property
     */
    protected function addProperty($type, $name): Property
    {
        $validator = new Validator($type);
        $column = new Column($this->table, $name, $type);

        $this->table->pushColumn($column);

        $property = new Property(
            $name,
            $type,
            $column,
            $validator
        );

        $this->properties[$name] = $property;
        $this->validators[$name] = $validator;

        return $property;
    }
}
