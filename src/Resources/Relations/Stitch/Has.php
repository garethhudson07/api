<?php

namespace Api\Resources\Relations\Stitch;

use Stitch\Relations\Has as StitchRelation;

/**
 * Class HasMany
 * @package Api\Resources\Relations
 */
class Has extends Relation
{
    /**
     * @return mixed
     */
    public function make()
    {
        return (new StitchRelation(
            $this->name,
            $this->getLocalModel())
        )->foreignModel($this->getForeignmodel())
            ->boot();
    }

    /**
     * @return $this
     */
    public function pullKeys()
    {
        $key = $this->getForeignModel()->getTable()->getForeignKeyFor(
            $this->getLocalModel()->getTable()->getPrimaryKey()
        );

        $this->foreignKey = $key->getLocalColumn()->getName();
        $this->localKey = $key->getReferenceColumnName();

        return $this;
    }
}