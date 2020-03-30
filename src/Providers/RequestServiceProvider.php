<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Api\Container;
use Psr\Http\Message\ServerRequestInterface;
use Api\Config\Store as ConfigStore;
use Api\Config\Manager as ConfigManager;
use Api\Http\Requests\Factory;

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
        ServerRequestInterface::class,
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

        Container::alias('request', ServerRequestInterface::class);
    }

    /**
     * This is where the magic happens, within the method you can
     * access the container and register or retrieve anything
     * that you need to, but remember, every alias registered
     * within this method must be declared in the `$provides` array.
     */
    public function register()
    {
        $this->getContainer()->share(ServerRequestInterface::class, function ()
        {
            return (new Factory(
                $this->requestConfig,
                $this->specConfig)
            )->instance();
        });
    }
}
