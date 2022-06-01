<?php

namespace Api\Queries\Paths;

use Aggregate\Set;

class Pieces extends Set
{
    /**
     * @var string
     */
    protected string $delimiter = '.';

    /**
     * @return string
     */
    public function implode(): string
    {
        return implode($this->delimiter, array_map(function ($item) {
            return $item->getName();
        }, $this->items));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->implode();
    }
}
