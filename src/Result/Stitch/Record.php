<?php


namespace Api\Result\Stitch;

use Api\Result\Contracts\Record as Contract;
use Stitch\Result\Record as StitchResultRecord;
use Stitch\Result\Set as StitchResultSet;


class Record implements Contract
{
    protected $entity;

    /**
     * Record constructor.
     * @param mixed $entity
     */
    public function __construct(mixed $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return iterable
     */
    public function getRelations(): iterable
    {
        $output = [];

        foreach ($this->entity->getRelations() as $key => $relation) {
            if (is_null($relation)) {
                $relation[$key] = null;
                continue;
            }

            $output[$key] = $relation instanceof StitchResultSet ? (new Set())->build($relation) : new static($relation);
        }

        return $output;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->entity instanceof StitchResultRecord ? $this->entity->getData() : $this->entity->getAttributes();
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key): mixed
    {
        return $this->getAttributes()[$key] ?? null;
    }
}
