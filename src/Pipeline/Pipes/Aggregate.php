<?php

namespace Api\Pipeline\Pipes;

use Api\Collection;

class Aggregate extends Collection
{
    /**
     * @return mixed|null
     */
    public function last()
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    /**
     * @return mixed|null
     */
    public function penultimate()
    {
        return $this->items[count($this->items) - 2] ?? null;
    }

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function before(Pipe $pipe)
    {
        return array_slice($this->items, 0, array_search($pipe, $this->items));
    }

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function after(Pipe $pipe)
    {
        return array_slice($this->items, array_search($pipe, $this->items) + 1);
    }
}
