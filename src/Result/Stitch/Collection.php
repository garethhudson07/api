<?php


namespace Api\Result\Stitch;

use Api\Collection as BaseCollection;
use Stitch\Result\Set as StitchResultSet;

class Collection extends BaseCollection
{
    /**
     * @param StitchResultSet $resultSet
     * @return $this
     */
    public function build(StitchResultSet $resultSet): self
    {
        return $this->fill(array_map(function ($item)
        {
            return new Record($item);
        }, $resultSet->all()));
    }
}
