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
    protected function makeStitchRelation()
    {
        return new StitchRelation(
            $this->name,
            $this->localResource->getRepository()->getModel()
        );
    }
}