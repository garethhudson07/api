<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Api\Container;
use Api\Config\Manager as Config;
use Api\Guards\Contracts\Sentinel as SentinelInterface;
use Api\Guards\OAuth2\Authoriser as AuthoriserInterface;
use Api\Guards\Contracts\Key as KeyInterface;
use Api\Guards\OAuth2\Factory as OAuth2Factory;

class GuardServiceProvider extends AbstractServiceProvider
{
    protected $config;

    /**
     * @var array
     */
    protected $provides = [
        SentinelInterface::class,
        AuthoriserInterface::class,
        KeyInterface::class
    ];

    /**
     * GuardServiceProvider constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        Container::alias('guard.sentinel', SentinelInterface::class);
        Container::alias('guard.authoriser', AuthoriserInterface::class);
        Container::alias('guard.key', KeyInterface::class);
    }

    /**
     * Register services
     */
    public function register()
    {
        switch ($this->config->using()) {
            case 'OAuth2':
                $this->bindOAuth2();
                break;

            default:
                $this->getContainer()->add(SentinelInterface::class, false);
                $this->getContainer()->add(AuthoriserInterface::class, false);
                $this->getContainer()->add(KeyInterface::class, false);
        }
    }

    /**
     * Register OAuth2
     */
    protected function bindOAuth2(): void
    {
        $container = $this->getContainer();

        $factory = new OAuth2Factory(
            $this->getContainer(),
            $this->config->service('OAuth2')
        );

        $container->share(SentinelInterface::class, function () use ($factory)
        {
            return $factory->sentinel();
        });

        $container->share(AuthoriserInterface::class, function () use ($factory)
        {
            return $factory->authoriser();
        });

        $container->share(KeyInterface::class, function () use ($factory)
        {
            return $factory->key();
        });
    }
}
