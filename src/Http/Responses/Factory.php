<?php

namespace Api\Http\Responses;

use Api\Http\Responses\Contracts\Factory as FactoryInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class Factory implements FactoryInterface
{
    protected $psr17Factory;

    /**
     * @param string $content
     * @return ResponseInterface
     */
    public function make(string $content = ''): ResponseInterface
    {
        $response = $this->psr7Response()->withBody($this->psr7Stream());

        if ($content) {
            $response->getBody()->write((string) $content);
        }

        return $response;
    }

    /**
     * @param string $content
     * @return ResponseInterface
     */
    public function json(string $content = ''): ResponseInterface
    {
        return $this->make($content)->withHeader('Content-Type', 'application/vnd.api+json');
    }

    /**
     * @return Psr17Factory
     */
    public function psr17Factory(): Psr17Factory
    {
        if (!$this->psr17Factory) {
            $this->psr17Factory = new Psr17Factory();
        }

        return $this->psr17Factory;
    }

    /**
     * @return ResponseInterface
     */
    public function psr7Response(): ResponseInterface
    {
        return $this->psr17Factory()->createResponse();
    }

    /**
     * @return mixed
     */
    public function psr7Stream()
    {
        return $this->psr17Factory()->createStream();
    }

    /**
     * @return SapiEmitter
     */
    public function emitter()
    {
        return new SapiEmitter();
    }
}
