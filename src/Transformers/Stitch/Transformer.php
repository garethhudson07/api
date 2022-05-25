<?php

namespace Api\Transformers\Stitch;

use Api\Transformers\Contracts\Transformer as Contract;
use Api\Result\Contracts\Record;

class Transformer implements Contract
{
    protected $schema;

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    /**
     * @param Record $record
     * @return array
     */
    public function transform(Record $record): array
    {
        $attributes = $record->getAttributes();
        $transformed = [];

        foreach ($this->schema->getProperties() as $property) {
            $transformed[$property->getName()] = $attributes[$property->getColumn()->getName()] ?? null;
        }

        return $transformed;
    }
}