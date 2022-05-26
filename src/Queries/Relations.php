<?php

namespace Api\Queries;

use Aggregate\Set;

class Relations extends Set
{
    /**
     * @param string $name
     * @return Relation|null
     */
    public function get(string $name): ?Relation
    {
        foreach ($this->items as $item) {
            if ($item->getName() === $name) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param string $path
     * @return Relation|null
     */
    public function pull(string $path): ?Relation
    {
        $pieces = explode('.', $path);

        if ($relation = $this->get($pieces[0])) {
            if (count($pieces) > 1) {
                return $relation->pullRelation($pieces[1]);
            }

            return $relation;
        }

        return null;
    }

    /**
     * @return array
     */
    public function collapse(): array
    {
        $items = $this->items;

        foreach ($this->items as $item) {
            $items = array_merge($items, $item->getRelations()->collapse());
        }

        return $items;
    }
}
