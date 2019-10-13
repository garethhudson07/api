<?php

namespace Api\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Api\Config\Manager as Config;
use Api\Guards\Contracts\Sentinel;
use Api\Guards\OAuth2\Factory as OAuth2Factory;

class GuardServiceProvider extends AbstractServiceProvider
{
    protected $config;

    /**
     * @var array
     */
    protected $provides = [
        Sentinel::class
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
     * Register services
     */
    public function register()
    {
        switch ($this->config->using()) {
            case 'OAuth2':
                $this->bindOAuth2();
                break;

            default:
                $this->getContainer()->add(Sentinel::class, false);
        }
    }

    /**
     * Register OAuth2
     */
    protected function bindOAuth2(): void
    {
        $this->getContainer()
            ->share(Sentinel::class, function ()
            {
                return (new OAuth2Factory(
                    $this->getContainer(),
                    $this->config->service('OAuth2')
                ))->sentinel();
            });
    }
}