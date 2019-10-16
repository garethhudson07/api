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
    protected $stitchRelation;

    /**
     * @return $this|mixed
     */
    public function boot()
    {
        $this->stitchRelation = $this->makeStitchRelation()->foreignModel($this->getForeignModel());

        if ($this->localKey) {
            $this->stitchRelation->localKey($this->localKey);
        }

        if ($this->foreignKey) {
            $this->stitchRelation->foreignKey($this->foreignKey);
        }

        $this->stitchRelation->boot();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStitchRelation()
    {
        return $this->stitchRelation;
    }

    /**
     * @return mixed
     */
    public function getLocalKey()
    {
        return $this->stitchRelation->getLocalKey()->getName();
    }

    /**
     * @return mixed
     */
    public function getForeignKey()
    {
        return $this->stitchRelation->getForeignKey()->getName();
    }

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
    abstract protected function makeStitchRelation();
}
