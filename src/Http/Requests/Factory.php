<?php

namespace Api\Http\Requests;

use Api\Http\Requests\Contracts\Factory as FactoryInterface;
use Api\Config\Manager as ConfigManager;
use Api\Config\Store as ConfigStore;
use Api\Queries\Query;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

class Factory implements FactoryInterface
{
    protected $requestConfig;

    protected $specConfig;

    protected $instance;

    /**
     * Factory constructor.
     * @param ConfigStore $requestConfig
     * @param ConfigManager $specConfig
     */
    public function __construct(ConfigStore $requestConfig, ConfigManager $specConfig)
    {
        $this->requestConfig = $requestConfig;
        $this->specConfig = $specConfig;
    }

    /**
     * @return ConfigStore
     */
    public static function config()
    {
        return (new ConfigStore())->accepts('base', 'prefix');
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function instance()
    {
        if (!$this->instance) {
            if ($this->requestConfig->has('instance')) {
                $this->instance = $this->requestConfig->instance;
            } else {
                $psr17Factory = new Psr17Factory();

                $this->instance = (new ServerRequestCreator(
                    $psr17Factory,
                    $psr17Factory,
                    $psr17Factory,
                    $psr17Factory
                ))->fromGlobals();
            }
        }

        return $this->instance;
    }

    /**
     * @return ServerRequestInterface
     */
    public function prepare()
    {
        $request = $this->instance();
        $request = $request->withAttribute('segments', Parser::segments($request->getUri()->getPath()));

        switch ($request->getMethod()) {
            case 'DELETE':
                return $request;

            case 'GET':
                return $request->withAttribute('query', Query::extract($request, $this->specConfig));

            default:
                return $request->withParsedBody(
                    json_decode(
                        file_get_contents("php://input"),
                        true
                    ) ?: []
                );
        }
    }
}
