<?php

namespace Api\Http\Requests;

use Aggregate\Map;
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
        $parser = $this->container->get('request.parser');
        $request = $request->withAttribute('segments', Parser::segments($request->getUri()->getPath()));
        $request = $request->withAttribute(
            'parsedQuery',
            Query::extract(
                $parser,
                $request,
                $this->specConfig
            )
        );

        if ($request->getMethod() !== 'GET') {
            $body = json_decode(
                file_get_contents("php://input"),
                true
            ) ?: [];

            if (($request->getServerParams()['CONTENT_TYPE'] ?? null) === 'application/vnd.api+json') {
                $request = $request->withAttribute('parsedId', $parser->id($body))
                    ->withAttribute('parsedType', $parser->type($body))
                    ->withParsedBody((new Map())->fill($parser->attributes($body)));
            }
        }

        return $request;
    }
}
