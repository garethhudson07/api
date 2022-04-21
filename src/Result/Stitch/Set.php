<?php


namespace Api\Result\Stitch;

use Aggregate\Set as BaseSet;
use Stitch\Result\Set as StitchResultSet;

class Set extends BaseSet
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
