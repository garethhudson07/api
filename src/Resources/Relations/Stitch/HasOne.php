<?php

namespace Api\Resources\Relations\Stitch;

use Stitch\Relations\HasOne as StitchRelation;

/**
 * Class HasMany
 * @package Api\Resources\Relations
 */
class HasOne extends Relation
{
    /**
     * @return mixed
     */
    protected function makeStitchRelation()
    {
        return new StitchRelation(
            $this->name,
            $this->localResource->getRepository()->getModel()
        );
    }
}