<?php

namespace Api\Config;

use Closure;

class Store
{
    protected $accepts = [];

    protected $values = [];

    protected $parent;

    /**
     *
     *
     * @param array ...$arguments
     * @return $this
     */
    public function accepts(...$arguments)
    {
        $this->accepts = array_unique(array_merge($this->accepts, $arguments));

        return $this;
    }

    /**
     *
     *
     * @param string $key
     * @return bool
     */
    public function accepting(string $key)
    {
        return (in_array($key, $this->accepts) || ($this->parent && $this->parent->accepting($key)));
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key)
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function pulls(string $key)
    {
        return (!$this->has($key) && ($this->parent && ($this->parent->has($key) || $this->parent->pulls($key))));
    }

    /**
     * Inherit from another Service
     *
     * @param Store $config
     * @return $this
     */
    public function inherit(Store $config)
    {
        $this->parent = $config;

        return $this;
    }

    /**
     * @return Store
     */
    public function extend()
    {
        return (new static())->inherit($this);
    }

    /**
     * Set a configuration value
     *
     * @param string $key
     * @param $value
     * @return $this
     */
    public function set(string $key, $value)
    {
        if ($this->accepting($key)) {
            $this->values[$key] = $value;
        }

        return $this;
    }

    /**
     * Get a configuration value
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $value = $this->has($key)
            ? $this->values[$key]
            : ($this->parent ? $this->parent->get($key) : null);

        if ($value instanceof Closure) {
            $value = $value();
            $this->set($key, $value);
        }

        return $value;
    }

    /**
     * Get a configuration value via property access
     *
     * @param string $key
     * @return mixed|null
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Set a configuration value via method access
     *
     * @param $name
     * @param $arguments
     * @return Store
     */
    public function __call($name, $arguments)
    {
        return $this->set($name, $arguments ? $arguments[0] : true);
    }
}
