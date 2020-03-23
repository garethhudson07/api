<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Api\Container;
use Api\Config\Service as ConfigService;
use Api\Config\Manager as ConfigManager;
use Api\Http\Requests\Factory;
use Api\Http\Requests\Contracts\Factory as FactoryInterface;

class RequestServiceProvider extends AbstractServiceProvider
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
        FactoryInterface::class,
    ];

    /**
     * RequestServiceProvider constructor.
     * @param ConfigService $requestConfig
     * @param ConfigManager $specConfig
     */
    public function __construct(ConfigService $requestConfig, ConfigManager $specConfig)
    {
        $this->requestConfig = $requestConfig;
        $this->specConfig = $specConfig;

        Container::alias('request.factory', FactoryInterface::class);
    }

    /**
     * This is where the magic happens, within the method you can
     * access the container and register or retrieve anything
     * that you need to, but remember, every alias registered
     * within this method must be declared in the `$provides` array.
     */
    public function register()
    {
        $this->getContainer()->share(FactoryInterface::class, function ()
        {
            return new Factory($this->requestConfig, $this->specConfig);
        });
    }
}
