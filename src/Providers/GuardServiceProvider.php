<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Api\Container;
use Api\Config\Manager as Config;
use Api\Guards\Contracts\Sentinel as SentinelInterface;
use Api\Guards\OAuth2\Factory as OAuth2Factory;

class GuardServiceProvider extends AbstractServiceProvider
{
    protected $config;

    /**
     * @var array
     */
    protected $provides = [
        SentinelInterface::class
    ];

    /**
     * GuardServiceProvider constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        Container::alias('guard.sentinel', SentinelInterface::class);
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
        }
    }

    /**
     * Register OAuth2
     */
    protected function bindOAuth2(): void
    {
        $this->getContainer()
            ->share(SentinelInterface::class, function ()
            {
                return (new OAuth2Factory(
                    $this->getContainer(),
                    $this->config->service('OAuth2')
                ))->sentinel();
            });
    }
}
