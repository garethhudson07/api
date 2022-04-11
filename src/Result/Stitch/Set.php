<?php

namespace Api\Result\Stitch;

use Api\Result\Contracts\Set as Contract;
use Api\Collection;

class Set extends Collection implements Contract
{
    /**
     * @return array
     */
    public function transform($transformer): array
    {
        return array_map(function ($item) use ($transformer)
        {
            return $transformer->toArray($item);
        }, $this->items);
    }
}
