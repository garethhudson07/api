<?php

namespace Api\Resources;

use Api\Registry as AbstractRegistry;
use Api\Container;
use Closure;

class Registry extends AbstractRegistry
{
    protected $factory;

    /**
     * Registry constructor.
     * @param Container $container
     * @param Factory $factory
     */
    public function __construct(Container $container, Factory $factory)
    {
        parent::__construct($container);

        $this->factory = $factory;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function resolve(string $name)
    {
        $item = $this->bindings[$name];

        if ($item instanceof Closure) {
            $item = $item($this->factory)->name($name);
            $this->items[$name] = $item;

            return $item;
        }

        return parent::resolve($name);
    }
}
