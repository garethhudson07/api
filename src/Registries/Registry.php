<?php

namespace Api\Registries;

use Aggregate\Aggregate;
use Api\Container;
use Closure;

/**
 * Class Registry
 * @package Api
 */
class Registry extends Aggregate
{
    /**
     * @var Container
     */
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
    public function bind($key, $value): Registry
    {
        $binding = (new Binding($this))->value($value);

        $this->put($key, $binding);

        // The supplied key may have been null, provide the binding object the actual key
        $binding->key(
            array_search($binding, $this->items)
        );

        return $this;
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        if ($this->offsetExists($offset)) {
            $item = $this->items[$offset];

            if ($item instanceof Binding) {
                return $item->resolve();
            }

            return $item;
        }

        return null;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name): mixed
    {
        return $this->offsetGet($name);
    }

    /**
     * @return Iterator
     */
    public function getIterator(): Iterator
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

    /**
     * @param mixed $key
     * @param mixed $value
     * @return static
     */
    public function put(mixed $key, mixed $value): static
    {
        $this->offsetSet($key, $value);

        return $this;
    }
}
