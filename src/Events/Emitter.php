<?php

namespace Api\Events;

use Api\Events\Contracts\Emitter as EmitterInterface;
use Api\Events\Contracts\Event as EventInterface;
use Api\Events\Factory;
use League\Event\Emitter as BaseEmitter;
use Api\Events\Listeners\Aggregate as ListenerAggregate;
use Api\Container;
use Closure;

class Emitter implements EmitterInterface
{
    protected $factory;

    protected $baseEmitter;

    protected $listeners;

    protected $parent;

    /**
     * Emitter constructor.
     * @param \Api\Events\Factory $factory
     * @param BaseEmitter $baseEmitter
     * @param ListenerAggregate $listeners
     */
    public function __construct(Factory $factory, BaseEmitter $baseEmitter, ListenerAggregate $listeners)
    {
        $this->factory = $factory;
        $this->baseEmitter = $baseEmitter;
        $this->listeners = $listeners;
    }

    /**
     * @return EmitterInterface
     */
    public function extend(): EmitterInterface
    {
        return $this->factory->extendEmitter($this);
    }

    /**
     * @param EmitterInterface $parent
     * @return $this
     */
    public function bubble(EmitterInterface $parent): EmitterInterface
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @param string $event
     * @param Closure|null $closure
     * @return EventInterface
     */
    public function emit(string $event, ?Closure $closure = null): EventInterface
    {
        $event = $this->factory->event($event);

        if ($closure) {
            $closure($event->getPayload());
        }

        return $this->fire($event);
    }

    /**
     * @param EventInterface $event
     * @return EventInterface
     */
    public function fire(EventInterface $event): EventInterface
    {
        $this->listeners->resolve($event);

        $event = $this->baseEmitter->emit($event);

        if (!$event->isPropagationStopped() && $this->parent) {
            $event = $this->parent->fire($event);
        }

        return $event;
    }

    /**
     * @param string $event
     * @param $listener
     * @return $this
     */
    public function listen(string $event, $listener)
    {
        $this->listeners->add($event, $listener);

        return $this;
    }
}
