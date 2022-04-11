<?php

namespace Api\Transformers\Stitch;

class Transformer
{
    protected $schema;

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return array
     */
    public function record($record): array
    {
        $raw = $record->getData();
        $transformed = [];

        foreach ($this->schema->getProperties() as $property) {
            $transformed[$property->getName()] = $raw[$property->getColumn->getName()] ?? null;

            foreach ($record->getRelations() as $name => $relation) {
                $transformed[$name] = $relation->transform();
            }
        }

        return $transformed;
    }
}
