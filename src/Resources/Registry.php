<?php

namespace Api\Resources;

use Api\Registry as AbstractRegistry;
use Closure;

class Registry extends AbstractRegistry
{
    protected $factory;

    /**
     * Registry constructor.
     * @param Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function resolve(string $name)
    {
        $item = $this->items[$name];

        if ($item instanceof Closure) {
            $item = $item($this->factory)->name($name);
            $this->items[$name] = $item;
        }

        return $item;
    }
}
