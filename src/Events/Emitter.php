<?php

namespace Api\Events;

use Api\Events\Contracts\Emitter as EmitterInterface;
use Api\Events\Contracts\Event as EventInterface;
use League\Event\Emitter as BaseEmitter;
use Api\Events\Listeners\Aggregate as ListenerAggregate;
use Api\Container;
use Closure;

class Emitter implements EmitterInterface
{
    protected $baseEmitter;

    protected $listeners;

    protected $parent;

    /**
     * Emitter constructor.
     * @param BaseEmitter $baseEmitter
     * @param ListenerAggregate $listeners
     */
    public function __construct(BaseEmitter $baseEmitter, ListenerAggregate $listeners)
    {
        $this->baseEmitter = $baseEmitter;
        $this->listeners = $listeners;
    }

    /**
     * @param Container $container
     * @return EmitterInterface
     */
    public function extend(Container $container): EmitterInterface
    {
        return Factory::emitter($container)->bubble($this);
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
        $event = Factory::event($event);

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
