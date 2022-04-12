<?php

namespace Api\Pipeline\Pipes;

use Aggregate\Set;

class Aggregate extends Set
{
    /**
     * @param Pipe $pipe
     * @return array
     */
    public function beforePipe(Pipe $pipe)
    {
        return array_slice($this->items, 0, array_search($pipe, $this->items));
    }

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function afterPipe(Pipe $pipe)
    {
        return array_slice($this->items, array_search($pipe, $this->items) + 1);
    }
}
