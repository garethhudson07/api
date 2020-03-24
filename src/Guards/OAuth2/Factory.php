<?php

namespace Api\Guards\OAuth2;

use Api\Guards\OAuth2\League\Factory as LeagueFactory;
use Api\Config\Service;
use Api\Container;

class Factory
{
    protected $container;

    protected $config;

    protected $leagueFactory;

    public function __construct(Container $container, Service $config)
    {
        $this->container = $container;
        $this->config = $config;
        $this->leagueFactory = new LeagueFactory($config);
    }

    /**
     * @return Service
     */
    public static function config()
    {
        return LeagueFactory::config()->accepts('userRepository');
    }

    /**
     * @return Sentinel
     */
    public function sentinel()
    {
        return new Sentinel(
            $this->container->get('request.factory')->instance(),
            $this->key()
        );
    }

    /**
     * @return Key
     */
    public function key()
    {
        return new Key(
            $this->leagueFactory->resourceServer(),
            $this->container->get('request.factory')->instance(),
            $this->config->get('userRepository')
        );
    }

    /**
     * @return Authoriser
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function authoriser()
    {
        return new Authoriser(
            $this->leagueFactory->authorisationServer(),
            $this->container->get('request.factory')->instance()
        );
    }
}
