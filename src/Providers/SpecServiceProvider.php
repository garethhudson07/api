<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Api\Config\Manager as Config;
use Api\Specs\Contracts\Representations\Factory as RepresentationFactoryInterface;
use Api\Specs\Contracts\Parser as ParserInterface;
use Api\Specs\JsonApi\Representations\Factory as JsonApiRepresentationFactory;
use Api\Specs\JsonApi\Parser as JsonApiParser;
use Api\Container;

class SpecServiceProvider extends AbstractServiceProvider
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
        RepresentationFactoryInterface::class,
        ParserInterface::class,
    ];

    /**
     * GuardServiceProvider constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        Container::alias('representationFactory', RepresentationFactoryInterface::class);
        Container::alias('request.parser', ParserInterface::class);
    }

    /**
     * This is where the magic happens, within the method you can
     * access the container and register or retrieve anything
     * that you need to, but remember, every alias registered
     * within this method must be declared in the `$provides` array.
     */
    public function register()
    {
        switch ($this->config->using()) {
            case 'jsonApi':
                $this->bindJsonApi();
                break;
        }
    }

    /**
     *
     */
    protected function bindJsonApi()
    {
        $container = $this->getContainer();

        $container->share(RepresentationFactoryInterface::class, function () use ($container)
        {
            return new JsonApiRepresentationFactory($container->get('request.instance'));
        });

        $container->share(ParserInterface::class, function ()
        {
            return new JsonApiParser();
        });
    }
}
