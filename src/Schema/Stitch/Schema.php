<?php

namespace Api\Schema\Stitch;

use Api\Schema\Schema as BaseSchema;
use Api\Schema\Validation\Factory as ValidatorFactory;
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
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;

        $this->mapTable();;
    }

    /**
     *
     */
    protected function mapTable(): void
    {
        foreach ($this->table->getColumns() as $column) {
            $property = new Property(
                $column->getName(),
                ValidatorFactory::make($column->getType()),
                $column
            );

            $this->addProperty($property);
        }
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
     * @param $property
     * @return $this
     */
    public function addProperty($property): self
    {
        parent::addProperty($property);

        $this->table->pushColumn($property->getColumn());

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     */
    protected function makeProperty(string $name, string $type)
    {
        return new Property(
            $name,
            ValidatorFactory::make($type),
            new Column($this->table, $name, $type)
        );
    }
}
