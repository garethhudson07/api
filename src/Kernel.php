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
     * @param Container $container
     * @param Configs $configs
     */
    public function __construct(Container $container, Configs $configs)
    {
        $this->container = $container;
        $this->configs = $configs;
    }

    /**
     * @return Kernel
     */
    public static function make()
    {
        return new static(
            new Container(),
            new Configs()
        );
    }

    /**
     * @return Kernel
     */
    public function extend()
    {
        return new static(
            (new Container)->delegate($this->container),
            $this->configs->extend()
        );
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

    /**
     * @param string $name
     * @param $config
     * @return $this
     */
    public function addConfig(string $name, $config)
    {
        $this->configs->put($name, $config);

        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getConfig(string $name)
    {
        return $this->configs->get($name);
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
     * @param mixed ...$arguments
     * @return $this
     */
    public function bind(...$arguments)
    {
        $this->container->add(...$arguments);

        return $this;
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
     * @param mixed ...$ids
     * @return array
     */
    public function resolveMany(...$ids)
    {
        return array_map(function ($id)
        {
            return $this->resolve($id);
        }, $ids);
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
}
