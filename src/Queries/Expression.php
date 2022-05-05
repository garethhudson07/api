<?php

namespace Api\Queries;

use Closure;

/**
 * Class Where
 * @package Stitch\DBAL\Builders
 */
class Expression
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param string $operator
     * @param $constraint
     * @return $this
     */
    public function add(string $operator, $constraint)
    {
        $this->items[] = [
            'operator' => $operator,
            'constraint' => $constraint
        ];

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }
}
