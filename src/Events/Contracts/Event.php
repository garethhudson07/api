<?php

namespace Api\Events\Contracts;

use Api\Events\Payload;
use League\Event\EventInterface as BaseInterface;

interface Event extends BaseInterface
{
    public function payload(Payload $payload);

    /**
     * @return mixed
     */
    public function getPayload(): Payload;
}
