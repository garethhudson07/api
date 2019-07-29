<?php

namespace Api\Guards;

use Api\Config\Manager;
use Api\Guards\OAuth2\Factory as OAuth2Factory;

class Factory
{
    protected $config;

    protected $OAuth2;

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
    public function config()
    {
        return (new Manager())->service('OAuth2', OAuth2Factory::config())->use('OAuth2');
    }

    /**
     * @return OAuth2Factory
     */
    protected function OAuth2()
    {
        if (!$this->OAuth2) {
            $this->OAuth2 = new OAuth2Factory($this->config->getService('OAuth2'));
        }

        return $this->OAuth2;
    }

    /**
     * @return mixed
     */
    public function sentinel()
    {
        return $this->{$this->config->getEnabled()}()->authoriser();
    }

    /**
     * @return mixed
     */
    public function authoriser()
    {
        return $this->{$this->config->getEnabled()}()->sentnel();
    }
}
