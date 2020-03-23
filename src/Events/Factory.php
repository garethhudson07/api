<?php

namespace Api\Events;

use Api\Events\Contracts\Emitter as EmitterInterface;
use League\Event\Emitter as BaseEmitter;
use Api\Events\Listeners\Aggregate as ListenerAggregate;
use Api\Container;

class Factory
{
    /**
     * @param Container $container
     * @return EmitterInterface
     */
    public static function emitter(Container $container): EmitterInterface
    {
        $baseEmitter = new BaseEmitter();

        return new Emitter(
            $baseEmitter,
            new ListenerAggregate($container, $baseEmitter)
        );
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
