<?php

namespace Api\Config;

use Closure;

class Service
{
    protected $accepts = [];

    protected $values = [];

    protected $parent;

    /**
     * @param array ...$arguments
     * @return $this
     */
    public function accepts(...$arguments)
    {
        $this->accepts = array_unique(array_merge($this->accepts, $arguments));

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function accepting(string $key)
    {
        return (in_array($key, $this->accepts) || ($this->parent && $this->parent->accepting($key)));
    }

    /**
     * @param Service $config
     * @return $this
     */
    public function inherit(Service $config)
    {
        $this->parent = $config;

        return $this;
    }

    /**
     * @return Service
     */
    public function child()
    {
        return (new static())->inherit($this);
    }

    /**
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
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $value = array_key_exists($key, $this->values)
            ? $this->values[$key]
            : ($this->parent ? $this->parent->get($key) : null);

        if ($value instanceof Closure) {
            $value = $value();
            $this->set($key, $value);
        }

        return $value;
    }

    /**
     * @param $name
     * @param $arguments
     * @return Service
     */
    public function __call($name, $arguments)
    {
        return $this->set($name, $arguments ? $arguments[0] : true);
    }
}
