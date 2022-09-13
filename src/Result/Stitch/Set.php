<?php


namespace Api\Result\Stitch;

use Aggregate\Set as BaseSet;
use Api\Result\Contracts\Collection;
use Stitch\Result\Set as StitchResultSet;

class Set extends BaseSet implements Collection
{
    /**
     * @param StitchResultSet $resultSet
     * @return $this
     */
    public function build(StitchResultSet $resultSet): self
    {
        return $this->fill(array_map(function ($item) {
            return new Record($item);
        }, $resultSet->all()));
    }

    /**
     * @return iterable
     */
    public function getItems(): iterable
    {
        return $this->all();
    }

    /**
     * @return iterable
     */
    public function getMetaData(): iterable
    {
        return [];
    }
}
