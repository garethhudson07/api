<?php

namespace Api\Events;

use League\Event\Event as BaseEvent;
use Api\Events\Contracts\Event as EventInterface;

class Event extends BaseEvent implements EventInterface
{
    protected $payload;

    /**
     * @param Payload $payload
     * @return $this
     */
    public function payload(Payload $payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayload(): Payload
    {
        return $this->payload;
    }
}
