<?php

namespace Api\Config;

use Aggregate\Map;
use Closure;

class Aggregate extends Map
{
    /**
     * @param Aggregate $aggregate
     * @return $this
     */
    public function inherit(Aggregate $aggregate)
    {
        foreach ($aggregate as $name => $config) {
            $this->set(
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

    /**
     * @param mixed $key
     * @param mixed $value
     * @return static
     */
    public function put(mixed $key, mixed $value): static
    {
        $this->offsetSet($key, $value);

        return $this;
    }
}
