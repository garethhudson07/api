<?php

namespace Api\Registries;

use Api\Collection;
use Api\Container;
use Closure;

/**
 * Class Registry
 * @package Api
 */
class Registry extends Collection
{
    protected $container;

    /**
     * Registry constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function bind($key, $value)
    {
        $binding = (new Binding($this))->value($value);

        $this->offsetSet($key, $binding);

        // The supplied key may have been null, provide the binding object the actual key
        $binding->key(
            array_search($binding, $this->items)
        );

        return $this;
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        if ($this->has($key)) {
            $item = $this->items[$key];

            if ($item instanceof Binding) {
                return $item->resolve();
            }

            return $item;
        }

        return null;
    }

    public function getIterator()
    {
        return new Iterator($this->items);
    }

    /**
     * @param Binding
     * @return mixed
     */
    public function resolve(Binding $binding)
    {
        $value = $binding->getValue();

        if ($value instanceof Closure) {
            return $value();
        }

        return $this->container->get($value);
    }
}
