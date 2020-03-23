<?php

namespace Api\Http\Requests\Contracts;

use Api\Config\Service as ConfigService;
use Psr\Http\Message\ServerRequestInterface;

interface Factory
{
    /**
     * @return ConfigService
     */
    public static function config();

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function instance();

    /**
     * @return ServerRequestInterface
     */
    public function prepare();
}
