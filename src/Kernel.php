<?php

namespace Api;

use Closure;
use League\Container\Container;
use Api\Config\Store as Configs;

class Kernel
{
    protected $container;

    protected $configs;

    /**
     * Kernel constructor.
     */
    public function __construct()
    {
        $this->container = new Container;
        $this->configs = new Configs;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return Configs
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    public function addConfig()
    {

    }

    /**
     * @param string $name
     * @param Closure $callback
     */
    public function configure(string $name, Closure $callback)
    {
        return $this->configs->configure($name, $callback);
    }

    /**
     * @param Kernel $kernel
     * @return Kernel
     */
    public function delegate(Kernel $kernel)
    {
        $this->container->delegate($kernel->getContainer());
        $this->configs->delegate($kernel->getConfigs());

        return $this;
    }

    public function bind()
    {

    }
}
