<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Api\Container;
use Api\Http\Responses\Contracts\Factory as FactoryInterface;
use Api\Http\Responses\Factory;

class ResponseServiceProvider extends AbstractServiceProvider
{
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
     * ResponseServiceProvider constructor.
     */
    public function __construct()
    {
        Container::alias('response.factory', FactoryInterface::class);
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
            return new Factory;
        });
    }
}
