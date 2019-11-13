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
    public function makeStitchRelation()
    {
        return new StitchRelation(
            $this->name,
            $this->localResource->getRepository()->getModel()
        );
    }
}