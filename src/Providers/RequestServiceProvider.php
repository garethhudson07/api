<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Container;
use Api\Http\Requests\Contracts\Factory as FactoryInterface;
use Api\Config\Store as ConfigStore;
use Api\Config\Manager as ConfigManager;
use Api\Http\Requests\Factory;

class RequestServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    protected $requestConfig;

    protected $specConfig;

    /**
     * The provided array is a way to let the container
     * know that a service is provided by this service
     * provider. Every service that is registered via
     * this service provider must have an alias added
     * to this array or it will be ignored.
     *
     * @var array
     */
    protected $provides = [
        ServerRequestInterface::class,
        FactoryInterface::class
    ];

    /**
     * RequestServiceProvider constructor.
     * @param ConfigStore $requestConfig
     * @param ConfigManager $specConfig
     */
    public function __construct(ConfigStore $requestConfig, ConfigManager $specConfig)
    {
        $this->requestConfig = $requestConfig;
        $this->specConfig = $specConfig;

        Container::alias('request.factory', FactoryInterface::class);
        Container::alias('request.instance', ServerRequestInterface::class);
    }

    /**
     * In much the same way, this method has access to the container
     * itself and can interact with it however you wish, the difference
     * is that the boot method is invoked as soon as you register
     * the service provider with the container meaning that everything
     * in this method is eagerly loaded.
     *
     * If you wish to apply inflectors or register further service providers
     * from this one, it must be from a bootable service provider like
     * this one, otherwise they will be ignored.
     */
    public function boot() {}

    /**
     * This is where the magic happens, within the method you can
     * access the container and register or retrieve anything
     * that you need to, but remember, every alias registered
     * within this method must be declared in the `$provides` array.
     */
    public function register()
    {
        $container = $this->getContainer();

        $container->share(FactoryInterface::class, function () use ($container)
        {
            return (new Factory($container, $this->requestConfig, $this->specConfig));
        });

        $container->share(ServerRequestInterface::class, function () use ($container)
        {
            $factory = $container->get('request.factory');

            return $factory->prepare($factory->make());
        });
    }
}
