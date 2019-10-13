<?php

namespace Api\Specs;

use Api\Config\Manager;
use Api\Config\Service;
use Api\Specs\JsonApi\Factory as JsonApiFactory;

class Factory
{
    protected $config;

    protected $jsonApi;

    /**
     * Factory constructor.
     * @param Manager $config
     */
    public function __construct(Manager $config)
    {
        $this->config = $config;
    }

    /**
     * @return Manager
     */
    public static function config()
    {
        return (new Manager())->add('jsonApi', JsonApiFactory::config())->use('jsonApi');
    }

    /**
     * @return JsonApiFactory
     */
    public function jsonApi()
    {
        if (!$this->jsonApi) {
            $this->jsonApi = new JsonApiFactory($this->config->service('jsonApi'));
        }

        return $this->jsonApi;
    }

    /**
     * @return mixed
     */
    public function representation()
    {
        return $this->{$this->config->enabled()}()->representation();
    }
}
