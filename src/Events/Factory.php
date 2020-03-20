<?php

namespace Api\Events;

use Api\Events\Contracts\Emitter as EmitterInterface;
use League\Event\Emitter as BaseEmitter;

class Factory
{
    /**
     * @return EmitterInterface
     */
    public static function emitter(): EmitterInterface
    {
        return new Emitter(new BaseEmitter());
    }

    /**
     * @param string $name
     * @return Event
     */
    public static function event(string $name): Event
    {
        return Event::named($name)->payload(static::payload());
    }

    /**
     * @return Payload
     */
    public static function payload()
    {
        return new Payload();
    }
}
