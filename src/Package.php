<?php

namespace Api;

use Api\Config\Manager as ConfigManager;
use Api\Guards\OAuth2\Factory as OAuth2Factory;
use Api\Specs\JsonApi\Factory as JsonApiFactory;
use Api\Http\Requests\Factory as RequestFactory;
use Stitch\Stitch;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\Container\ContainerInterface;
use Closure;

/**
 * Class Api
 * @package Api
 */
class Package
{
    protected $kernel;

    /**
     * Package constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->kernel = Kernel::make();

        $this->buildConfig();
    }

    /**
     * @param Closure $callback
     */
    public static function addConnection(Closure $callback)
    {
        Stitch::addConnection($callback);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function buildConfig()
    {
        $this->kernel->addConfig(
            'guard',
            (new ConfigManager())->add('OAuth2', OAuth2Factory::config())
        )->addConfig(
            'specification',
            (new ConfigManager())->add('jsonApi', JsonApiFactory::config())->use('jsonApi')
        )->addConfig(
            'request',
            RequestFactory::config()
        );

        return $this;
    }

    /**
     * @param string $name
     * @param Closure $callback
     * @return $this
     */
    public function configure(string $name, Closure $callback)
    {
        $this->kernel->configure($name, $callback);

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function bind(...$arguments)
    {
        $this->kernel->bind(...$arguments);

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function listen(...$arguments)
    {
        $this->kernel->listen(...$arguments);

        return $this;
    }

    /**
     * @param AbstractServiceProvider $provider
     * @return $this
     */
    public function addServiceProvider(AbstractServiceProvider $provider)
    {
        $this->kernel->addServiceProvider($provider);

        return $this;
    }

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function addDelegateContainer(ContainerInterface $container)
    {
        $this->kernel->addDelegateContainer($container);

        return $this;
    }

    /**
     * @return Api
     */
    public function api()
    {
        return new Api($this->kernel->extend());
    }
}
