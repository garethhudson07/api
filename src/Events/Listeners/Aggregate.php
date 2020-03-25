<?php

namespace Api\Events\Listeners;

use Api\Events\Factory;
use League\Event\Emitter as BaseEmitter;
use Api\Events\Contracts\Event as EventInterface;

class Aggregate
{
    protected $factory;

    protected $baseEmitter;

    protected $items = [];

    public function __construct(Factory $factory, BaseEmitter $baseEmitter)
    {
        $this->factory = $factory;
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
            $this->initEvent($event);
        }

        return $this;
    }

    /**
     * @param string $event
     */
    protected function initEvent(string $event)
    {
        $this->items[$event] = $this->factory->registry();
    }

    /**
     * @param string $event
     * @param $listener
     * @return $this
     */
    public function add(string $event, $listener)
    {
        if (!$this->has($event)) {
            $this->initEvent($event);
        }

        $registry = $this->items[$event];

        is_string($listener) ? $registry->bind(null, $listener) : $registry->push($listener);

        return $this;
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
                $this->baseEmitter->addListener($name, $listener);
            }

            $this->flush($name);
        }

        return $this;
    }
}
