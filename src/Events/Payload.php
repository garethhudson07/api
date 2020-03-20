<?php

namespace Api\Events;

class Payload
{
    protected $action;

    protected $attributes = [];

    /**
     * @param string $name
     * @return $this
     */
    public function action(string $name)
    {
        $this->action = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $name
     * @param $value
     * @return $this
     */
    public function set(string $name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function get(string $name)
    {
        if ($this->has($name)) {
            return $this->attributes[$name];
        }

        return null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return array_key_exists($name, $this->attributes);
    }
}
