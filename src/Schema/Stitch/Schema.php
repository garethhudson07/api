<?php

namespace Api\Schema\Stitch;

use Api\Schema\Schema as BaseSchema;
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
 */
class Schema extends BaseSchema
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * Schema constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->table = new Table();
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
     * @param string $name
     * @param string $type
     */
    protected function makeProperty(string $name, string $type)
    {
        $column = new Column($this->table, $name, $type);

        $this->table->pushColumn($column);

        return new Property(
            $name,
            $type,
            new Validator($type),
            $column
        );
    }
}
