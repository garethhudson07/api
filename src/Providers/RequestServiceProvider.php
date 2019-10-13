<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Api\Config\Manager as Config;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

class RequestServiceProvider extends AbstractServiceProvider
{
    protected $config;

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
        'Psr\Http\Message\ServerRequestInterface',
    ];

    /**
     * GuardServiceProvider constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * This is where the magic happens, within the method you can
     * access the container and register or retrieve anything
     * that you need to, but remember, every alias registered
     * within this method must be declared in the `$provides` array.
     */
    public function register()
    {
        $this->getContainer()->add('Psr\Http\Message\ServerRequestInterface', function ()
        {
            $psr17Factory = new Psr17Factory();

            return (new ServerRequestCreator(
                $psr17Factory,
                $psr17Factory,
                $psr17Factory,
                $psr17Factory
            ))->fromGlobals();
        });
    }
}
