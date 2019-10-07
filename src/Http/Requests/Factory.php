<?php

namespace Api\Http\Requests;

use Api\Config\Manager;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

class Factory
{
    protected $config;

    protected $baseRequest;

    /**
     * Factory constructor.
     * @param Manager $config
     */
    public function __construct(Manager $config)
    {
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @return $this
     */
    public function setBaseRequest(ServerRequestInterface $request)
    {
        $this->baseRequest = $request;

        return $this;
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function base()
    {
        if (!$this->baseRequest) {
            $psr17Factory = new Psr17Factory();

            $this->baseRequest = (new ServerRequestCreator(
                $psr17Factory,
                $psr17Factory,
                $psr17Factory,
                $psr17Factory
            ))->fromGlobals();
        }

        return $this->baseRequest;
    }

    /**
     * @return ServerRequestInterface
     */
    public function query()
    {
        $request = $this->base();

        $bag = Bag::parse(
            Raw::extract($request, $this->config)
        );

        return $request->withAttribute('segments', $bag->segments())
            ->withAttribute('relations', $bag->relations())
            ->withAttribute('filters', $bag->filters())
            ->withAttribute('sort', $bag->sort())
            ->withAttribute('limit', $bag->limit());
    }
}
