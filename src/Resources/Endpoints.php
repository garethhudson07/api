<?php

namespace Api\Resources;

use Exception;

class Endpoints
{
    protected $available = [];

    protected $except = [];

    protected $only = [];

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function add(...$arguments)
    {
        $this->available = array_merge($this->available, $arguments);

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function except(...$arguments)
    {
        $this->except = array_merge($this->except, $arguments);

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function only(...$arguments)
    {
        $this->only = array_merge($this->only, $arguments);

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function enabled(string $name)
    {
        if (!in_array($name, $this->available)) {
            return false;
        }

        if (count($this->only) && !in_array($name, $this->only)) {
            return false;
        }

        if (in_array($name, $this->except)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @throws Exception
     */
    public function verify(string $name)
    {
        if (!$this->enabled($name)) {
            throw new Exception("The $name endpoint is not available");
        }
    }
}
