<?php

namespace Api\Registries;

class Binding
{
    protected $registry;

    protected $key;

    protected $value;

    /**
     * Binding constructor.
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param $key
     * @return $this
     */
    public function key($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param $value
     * @return $this
     */
    public function value($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function resolve()
    {
        $resolved = $this->registry->resolve($this);

        $this->registry->put($this->key, $resolved);

        return $resolved;
    }
}
