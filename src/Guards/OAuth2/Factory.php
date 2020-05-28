<?php

namespace Api\Guards\OAuth2;

use Api\Guards\OAuth2\League\Factory as LeagueFactory;
use Api\Config\Store;
use Api\Container;
use Api\Repositories\Contracts\User as UserRepositoryInterface;

class Factory
{
    protected $container;

    protected $config;

    protected $leagueFactory;

    public function __construct(Container $container, Store $config)
    {
        $this->container = $container;
        $this->config = $config;
        $this->leagueFactory = new LeagueFactory($container, $config);
    }

    /**
     * @return Store
     */
    public static function config()
    {
        return LeagueFactory::config();
    }

    /**
     * @return Sentinel
     */
    public function sentinel()
    {
        return new Sentinel(
            $this->container->get('request.instance'),
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
            $this->container->get('request.instance'),
            $this->container->get(UserRepositoryInterface::class)
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
            $this->container->get('request.instance')
        );
    }
}
