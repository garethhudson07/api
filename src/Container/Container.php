<?php

namespace Api\Container;

use Api\Container\Bindings\Aggregate as Bindings;

class Container
{
    protected $bindings;

    protected $delegates = [];

    public function __construct()
    {
        $this->bindings = new Bindings();
    }

    public function bind(string $key, $value)
    {
        $this->bindings->add($key, $value);

        return $this;
    }
}