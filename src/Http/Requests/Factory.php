<?php

namespace Api\Http\Requests;

use Api\Container;
use Api\Http\Requests\Contracts\Factory as FactoryInterface;
use Api\Config\Manager as ConfigManager;
use Api\Config\Store as ConfigStore;
use Api\Queries\Query;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

class Factory implements FactoryInterface
{
    protected $container;

    protected $requestConfig;

    protected $specConfig;

    protected $instance;

    /**
     * Factory constructor.
     * @param Container $container
     * @param ConfigStore $requestConfig
     * @param ConfigManager $specConfig
     */
    public function __construct(Container $container, ConfigStore $requestConfig, ConfigManager $specConfig)
    {
        $this->container = $container;
        $this->requestConfig = $requestConfig;
        $this->specConfig = $specConfig;
    }

    /**
     * @return ConfigStore
     */
    public static function config(): ConfigStore
    {
        return (new ConfigStore())->accepts('base', 'prefix');
    }

    /**
     * @return ServerRequestInterface
     */
    public function make(): ServerRequestInterface
    {
        $psr17Factory = new Psr17Factory();

        return (new ServerRequestCreator(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        ))->fromGlobals();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function prepare(ServerRequestInterface $request): ServerRequestInterface
    {
        $request = $request->withAttribute('segments', Parser::segments($request->getUri()->getPath()));

        switch ($request->getMethod()) {
            case 'GET':
                $request = $request->withAttribute(
                    'query',
                    Query::extract(
                        $this->container->get('request.parser'),
                        $request,
                        $this->specConfig
                    )
                );
                break;

            default:
                $body = json_decode(
                    file_get_contents("php://input"),
                    true
                ) ?: [];

                if (array_key_exists('data', $body) && array_key_exists('attributes', $body['data'])) {
                    $body['data']['attributes'] = $this->container->get('request.parser')->attributes($body['data']['attributes']);
                }

                if (($request->getServerParams()['CONTENT_TYPE'] ?? null) === 'application/vnd.api+json') {
                    $request = $request->withParsedBody($body);
                }
        }

        return $request;
    }
}
