<?php

namespace Api\Events\Listeners;

use Api\Container;
use League\Event\Emitter as BaseEmitter;
use Api\Events\Contracts\Event as EventInterface;

class Aggregate
{
    protected $container;

    protected $baseEmitter;

    protected $items = [];

    public function __construct(Container $container, BaseEmitter $baseEmitter)
    {
        $this->container = $container;
        $this->baseEmitter = $baseEmitter;
    }

    /**
     * @param string $event
     * @return bool
     */
    protected function has(string $event)
    {
        return array_key_exists($event, $this->items);
    }

    /**
     * @param string $event
     * @return $this
     */
    protected function flush(string $event)
    {
        if ($this->has($event)) {
            $this->items[$event] = [];
        }

        return $this;
    }

    /**
     * @param string $event
     * @param $listener
     */
    public function add(string $event, $listener)
    {
        if (!$this->has($event)) {
            $this->items[$event] = [];
        }

        $this->items[$event][] = $listener;
    }

    /**
     * @param EventInterface $event
     * @return $this
     */
    public function resolve(EventInterface $event)
    {
        $name = $event->getName();

        if ($this->has($name)) {
            foreach ($this->items[$name] as $listener) {
                if (is_string($listener)) {
                    $listener = $this->container->get($listener);
                }

                $this->baseEmitter->addListener($name, $listener);
            }

            $this->flush($name);
        }

        return $this;
    }
}
