<?php

namespace Api\Events;

use Api\Events\Contracts\Emitter as EmitterInterface;
use League\Event\Emitter as BaseEmitter;
use Api\Events\Listeners\Aggregate as ListenerAggregate;
use Api\Container;
use Api\Registries\Registry;

class Factory
{
    protected $container;

    /**
     * Factory constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Container $container
     * @return static
     */
    public static function make(Container $container)
    {
        return new static($container);
    }

    /**
     * @return EmitterInterface
     */
    public function emitter(): EmitterInterface
    {
        $baseEmitter = new BaseEmitter();

        return new Emitter(
            $this,
            $baseEmitter,
            new ListenerAggregate($this, $baseEmitter)
        );
    }

    /**
     * @param EmitterInterface $emitter
     * @return EmitterInterface
     */
    public function extendEmitter(EmitterInterface $emitter)
    {
        return $this->emitter()->bubble($emitter);
    }

    /**
     * @param string $name
     * @return Event
     */
    public function event(string $name): Event
    {
        return Event::named($name)->payload($this->payload());
    }

    /**
     * @return Payload
     */
    public function payload()
    {
        return new Payload();
    }

    /**
     * @return Registry
     */
    public function registry()
    {
        return new Registry($this->container);
    }
}
