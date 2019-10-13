<?php

namespace Api;

use Closure;
use League\Container\Container;
use Api\Config\Store as Configs;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\Container\ContainerInterface;

class Kernel
{
    protected $container;

    protected $configs;

    /**
     * Kernel constructor.
     */
    public function __construct(Container $container, Configs $configs)
    {
        $this->container = $container;
        $this->configs = $configs;
    }

    public function addConfig(string $name)
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

    public function bind($id, $value)
    {

    }

    /**
     * @param $id
     * @return array|mixed|object
     */
    public function resolve($id)
    {
        return $this->container->get($id);
    }

    /**
     * @param AbstractServiceProvider $provider
     * @return $this
     */
    public function addServiceProvider(AbstractServiceProvider $provider)
    {
        $this->container->addServiceProvider($provider);

        return $this;
    }

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function addDelegateContainer(ContainerInterface $container)
    {
        $this->container->delegate($container);

        return $this;
    }

    /**
     * @param Kernel $kernel
     * @return Kernel
     */
    public function extend()
    {
        return new static(
            (new Container)->delegate($this->container),
            $this->configs->extend()
        );
    }
}
