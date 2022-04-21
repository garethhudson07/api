<?php

namespace Api\Queries;

use Aggregate\Set;

class Relations extends Set
{
    /**
     * @param string $name
     * @return null
     */
    public function pull(string $name)
    {
        foreach ($this->items as $item) {
            if ($item->getName() === $name) {
                return $item;
            }

            if ($relation = $item->pullRelation($name)) {
                return $relation;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function collapse()
    {
        $items = $this->items;

        foreach ($this->items as $item) {
            $items = array_merge($items, $item->getRelations()->collapse());
        }

        return $items;
    }
}
