<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Api\Pipeline\Pipeline;
use Api\Http\Requests\Factory as RequestFactory;
use Api\Http\Responses\Factory as ResponseFactory;

class PipelineServiceProvider extends AbstractServiceProvider
{
    /**
     * @var array
     */
    protected $provides = [
        Pipeline::class
    ];

    /**
     * Register services
     */
    public function register()
    {
        $this->getContainer()->share(Pipeline::class, function ()
        {
            return new Pipeline(
                $this->getContainer()->get(RequestFactory::class)->prepare(),
                $this->getContainer()->get(ResponseFactory::class)->json()
            );
        });
    }
}
