<?php

namespace Api\Config;

use Api\Collection;
use Closure;

class Store extends Collection
{
    /**
     * @param Store $collection
     * @return $this
     */
    public function delegate(Store $collection)
    {
        foreach ($collection as $name => $config) {
            $this->put(
                $name,

                $config->child()
            );
        }

        return $this;
    }

    /**
     * @return Store
     */
    public function child()
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
