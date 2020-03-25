<?php

namespace Api\Config;

use Api\Collection;
use Closure;

class Aggregate extends Collection
{
    /**
     * @param Aggregate $aggregate
     * @return $this
     */
    public function inherit(Aggregate $aggregate)
    {
        foreach ($aggregate as $name => $config) {
            $this->put(
                $name,
                $config->extend()
            );
        }

        return $this;
    }

    /**
     * @return Aggregate
     */
    public function extend()
    {
        return (new static())->inherit($this);
    }

    /**
     * @param string $name
     * @param Closure $callback
     */
    public function configure(string $name, Closure $callback)
    {
        $callback($this->items[$name]);
    }
}
