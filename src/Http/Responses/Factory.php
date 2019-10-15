<?php

namespace Api\Http\Responses;

use Nyholm\Psr7\Factory\Psr17Factory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class Factory
{
    protected $psr17Factory;

    /**
     * @param string $content
     * @return mixed
     */
    public function make(string $content = '')
    {
        $response = $this->psr7Response()->withBody($this->psr7Stream());

        if ($content) {
            $response->getBody()->write($content);
        }

        return $response;
    }

    /**
     * @param string $content
     * @return mixed
     */
    public function json(string $content = '')
    {
        return $this->make($content)->withHeader('Content-Type', 'application/json');
    }

    /**
     * @return mixed
     */
    public function psr17Factory()
    {
        if (!$this->psr17Factory) {
            $this->psr17Factory = new Psr17Factory();
        }

        return $this->psr17Factory;
    }

    /**
     * @return mixed
     */
    public function psr7Response()
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