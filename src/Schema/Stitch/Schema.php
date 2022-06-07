<?php

namespace Api\Schema\Stitch;

use Api\Schema\Schema as BaseSchema;
use Api\Schema\Property as BaseProperty;
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
     * @param BaseProperty $property
     * @return static
     */
    public function addProperty(BaseProperty $property): static
    {
        if ($property instanceof Property) {
            $this->table->pushColumn($property->getColumn());
        }

        return parent::addProperty($property);
    }

    /**
     * @param string $name
     * @param string $type
     * @return Property
     */
    protected function makeProperty(string $name, string $type): Property
    {
        return new Property(
            $name,
            $type,
            ValidatorFactory::make($type),
            new Column($this->table, $name, $type)
        );
    }
}
