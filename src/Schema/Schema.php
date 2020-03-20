<?php

namespace Api\Schema;

use Api\Schema\Validation\Validator;
use Stitch\DBAL\Schema\Column;
use Stitch\DBAL\Schema\Table;
use Api\Schema\Validation\Aggregate as Validators;

class Schema
{
    protected $table;

    protected $properties = [];

    protected $validators;

    public function __construct()
    {
        $this->table = new Table();
        $this->validators = new Validators();
    }

    /**
     * @param string $name
     * @return $this
     */
    public function table(string $name)
    {
        $this->table->name($name);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function connection(string $name)
    {
        $this->table->connection($name);

        return $this;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param array $input
     * @return bool
     * @throws \Exception
     */
    public function validate(array $input)
    {
        return $this->validators->run($input);
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

    /**
     * @param string $method
     * @param array $arguments
     * @return Property
     */
    public function __call(string $method, array $arguments)
    {
        return $this->addProperty($method, $arguments[0]);
    }
}
