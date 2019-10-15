<?php

namespace Api\Resources\Relations\Stitch;

use Api\Resources\Relations\Relation as BaseRelation;
use Stitch\Model;

/**
 * Class HasMany
 * @package Api\Resources\Relations
 */
abstract class Relation extends BaseRelation
{
    /**
     * @return Model
     */
    protected function getLocalModel(): Model
    {
        return $this->getLocalResource()->getRepository()->getModel();
    }

    /**
     * @return Model
     */
    protected function getForeignModel(): Model
    {
        return $this->getForeignResource()->getRepository()->getmodel();
    }

    /**
     * @return mixed
     */
    abstract public function pullKeys();
}
