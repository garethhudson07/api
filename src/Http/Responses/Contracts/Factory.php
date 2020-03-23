<?php

namespace Api\Http\Responses\Contracts;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

interface Factory
{
    /**
     * @param string $content
     * @return ResponseInterface
     */
    public function make(string $content = ''): ResponseInterface;

    /**
     * @return ResponseInterface
     */
    public function json(): ResponseInterface;

    /**
     * @return Psr17Factory
     */
    public function psr17Factory(): Psr17Factory;

    /**
     * @return ResponseInterface
     */
    public function psr7Response(): ResponseInterface;

    /**
     * @return mixed
     */
    public function psr7Stream();

    /**
     * @return SapiEmitter
     */
    public function emitter();
}
