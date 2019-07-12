<?php

namespace Api\Auth\OAuth2\League\Entities;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

class User implements UserEntityInterface
{
    use EntityTrait;

    /**
     * User constructor.
     * @param $identifier
     */
    public function __construct($identifier)
    {
        $this->setIdentifier($identifier);
    }
}