<?php

namespace Api\Config;

use Aggregate\Map;
use Exception;

class Manager
{
    protected $global;

    protected $stores;

    protected $using;

    protected $parent;

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->stores = new Map();
    }

    /**
     * Add a service
     *
     * @param string $name
     * @param Store $store
     * @return $this
     */
    public function add(string $name, Store $store)
    {
        $this->stores->set($name, $store);

        return $this;
    }

    /**
     * Determine if the Manager has a local copy of the service
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return $this->stores->has($name);
    }

    /**
     * Pull a store from the hierarchy
     *
     * @param string $name
     * @return mixed|null
     */
    public function service(string $name)
    {
        return $this->has($name)
            ? $this->stores->get($name)
            : ($this->parent ? $this->parent->service($name) : null);
    }

    /**
     * Set the name of the enabled service
     *
     * @param string $name
     * @return $this
     * @throws Exception
     */
    public function use(string $name)
    {
        if ($this->has($name) || ($this->parent && $this->parent->has($name))) {
            $this->using = $name;

            return $this;
        }

        throw new Exception("Cannot to use Unknown config $name");
    }

    /**
     * Determine the name of the enabled service
     *
     * @return mixed
     */
    public function using()
    {
        return $this->using ? $this->using : ($this->parent ? $this->parent->using() : null);
    }

    /**
     * Pull the enabled service from the hierarchy
     *
     * @return mixed|null
     */
    public function enabled()
    {
        return $this->service($this->using());
    }

    /**
     * Inherit from another Manager
     *
     * @param Manager $manager
     * @return $this
     */
    public function inherit(Manager $manager)
    {
        $this->parent = $manager;

        return $this;
    }

    /**
     * Create a new Manager that inherits from this one
     *
     * @return Manager
     */
    public function extend()
    {
        return (new static())->inherit($this);
    }

    /**
     * Get a configuration value from the enabled service
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        if ($store = $this->enabled()) {
            return $store->get($key);
        }

        return null;
    }

    /**
     * Get a configuration value from the enabled service via property access
     *
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Set a configuration value on the enabled service
     *
     * @param string $key
     * @param $value
     * @return $this
     */
    public function set(string $key, $value)
    {
        $using = $this->using();

        if ($this->has($using)) {
            $store = $this->service($using);
        } else {
            $store = $this->parent->service($using)->extend();
            $this->stores->set($using, $store);
        }

        $store->set($key, $value);

        return $this;
    }

    /**
     * Set a configuration value on the enabled service via method access
     *
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        return $this->set($name, $arguments[0]);
    }
}
