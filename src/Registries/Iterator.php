<?php

namespace Api\Registries;

use ArrayIterator;

class Iterator extends ArrayIterator
{
    public function current()
    {
        $current = parent::current();

        if ($current instanceof Binding) {
            $current = $current->resolve();
        }

        return $current;
    }
}
