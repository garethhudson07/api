<?php

namespace Api;

/**
 * Class Registry
 * @package Api
 */
class Registry extends Collection
{
    protected $container;

    /**
     * The items contained in the registry.
     *
     * @var array
     */
    protected $items = [];

    protected $bindings = [];

    /**
     * Registry constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function bind($key, $value)
    {
        if (is_null($key)) {
            $this->bindings[] = $value;
        } else {
            $this->bindings[$key] = $value;
        }

        return $this;
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        if ($this->has($key)) {
            return $this->items[$key];
        }

        if (array_key_exists($key, $this->bindings)) {
            return $this->resolve($key);
        }

        return null;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function resolve(string $name)
    {
        $item = $this->bindings[$name];

        if ($this->container->has($item)) {
            $item = $this->container->get($item);

            $this->items[$name] = $item;
        }

        return $item;
    }
}
