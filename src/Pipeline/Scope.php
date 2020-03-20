<?php

namespace Api\Pipeline;

use Api\Pipeline\Pipes\Pipe;
use Api\Resources\Relations\Relation;

class Scope
{
    protected $ancestor;

    protected $relation;

    /**
     * Scope constructor.
     * @param Pipe $ancestor
     * @param Relation $relation
     */
    public function __construct(Pipe $ancestor, Relation $relation)
    {
        $this->ancestor = $ancestor;
        $this->relation = $relation;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->relation->getForeignKey();
    }

    /**
     * @return mixed:null
     */
    public function getValue()
    {
        return $this->ancestor->getData()[$this->relation->getLocalKey()] ?? null;
    }
}
