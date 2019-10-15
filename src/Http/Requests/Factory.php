<?php

namespace Api\Http\Requests;

use Api\Config\Manager as ConfigManager;
use Api\Config\Service as ConfigService;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

class Factory
{
    protected $requestConfig;

    protected $specConfig;

    protected $instance;

    /**
     * Factory constructor.
     * @param ConfigService $requestConfig
     * @param ConfigManager $specConfig
     */
    public function __construct(ConfigService $requestConfig, ConfigManager $specConfig)
    {
        $this->requestConfig = $requestConfig;
        $this->specConfig = $specConfig;
    }

    /**
     * @return ConfigService
     */
    public static function config()
    {
        return (new ConfigService())->accepts('base', 'prefix');
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
    public function query()
    {
        $request = $this->instance();

        $bag = Bag::parse(
            Raw::extract($request, $this->specConfig)
        );

        return $request->withAttribute('segments', $bag->segments())
            ->withAttribute('relations', $bag->relations())
            ->withAttribute('fields', $bag->fields())
            ->withAttribute('filters', $bag->filters())
            ->withAttribute('sort', $bag->sort())
            ->withAttribute('limit', $bag->limit());
    }
}
