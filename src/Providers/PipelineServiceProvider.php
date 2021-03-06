<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Api\Container;
use Api\Pipeline\contracts\Pipeline as PipelineInterface;
use Api\Pipeline\Pipeline;

class PipelineServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        PipelineInterface::class
    ];

    /**
     * PipelineServiceProvider constructor.
     */
    public function __construct()
    {
        Container::alias('pipeline', PipelineInterface::class);
    }

    /**
     * Register services
     */
    public function register()
    {
        $container = $this->getContainer();

        $container->share(PipelineInterface::class, function () use ($container)
        {
            return new Pipeline(
                $container->get('request.instance'),
                $container->get('response.factory')->json()
            );
        });
    }
}
