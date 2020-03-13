<?php

namespace Api\Http\Responses;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class Factory
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
            $response->getBody()->write($content);
        }

        return $response;
    }

    /**
     * @return ResponseInterface
     */
    public function json(): ResponseInterface
    {
        return $this->make()->withHeader('Content-Type', 'application/json');
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
