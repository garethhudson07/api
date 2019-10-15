<?php

namespace Api;

use Closure;

/**
 * Class Registry
 * @package Api
 */
abstract class Registry
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function put(string $key, $value)
    {
        $this->items[$key] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param Closure $callback
     * @return $this
     */
    public function bind(string $name, Closure $callback)
    {
        $this->items[$name] = $callback;

        return $this;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function get($name)
    {
        return array_key_exists($name, $this->items) ? $this->resolve($name) : null;
    }

    /**
     * @param string $name
     * @return mixed
     */
    abstract public function resolve(string $name);
}