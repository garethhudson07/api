<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Api\Config\Manager as Config;
use Api\Specs\Contracts\Representation as RepresentationInterface;
use Api\specs\JsonApi\Representation as JsonApiRepresentation;


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
        RepresentationInterface::class,
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
        $this->getContainer()->share(RepresentationInterface::class, JsonApiRepresentation::class);
    }
}
