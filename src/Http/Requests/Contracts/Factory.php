<?php

namespace Api\Http\Requests\Contracts;

use Api\Config\Store as ConfigStore;
use Psr\Http\Message\ServerRequestInterface;

interface Factory
{
    /**
     * @return ConfigStore
     */
    public static function config();

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function instance();
}
