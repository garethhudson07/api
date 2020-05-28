<?php

namespace Api\Http\Requests\Contracts;

use Api\Config\Store as ConfigStore;
use Psr\Http\Message\ServerRequestInterface;

interface Factory
{
    /**
     * @return ConfigStore
     */
    public static function config(): ConfigStore;

    /**
     * @return ServerRequestInterface
     */
    public function make(): ServerRequestInterface;

    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function prepare(ServerRequestInterface $request): ServerRequestInterface;
}