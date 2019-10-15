<?php

namespace Api\Resources\Relations\Stitch;

use Stitch\Relations\BelongsTo as StitchRelation;

/**
 * Class HasMany
 * @package Api\Resources\Relations
 */
class BelongsTo extends Relation
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
        $key = $this->getLocalModel()->getTable()->getForeignKeyFor(
            $this->getForeignModel()->getTable()->getPrimaryKey()
        );

        $this->foreignKey = $key->getReferenceColumnName();
        $this->localKey = $key->getLocalColumn()->getName();

        return $this;
    }
}