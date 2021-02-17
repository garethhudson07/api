<?php

namespace Api;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Class Collection
 * @package Stitch
 */
class Collection implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * @param $value
     * @return self
     */
    public function push($value): self
    {
        $this->offsetSet(null, $value);

        return $this;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return self
     */
    public function put($key, $value): self
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * @param array $items
     * @return self
     */
    public function fill(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function has($key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * @param mixed $name
     * @return mixed|null
     */
    public function get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        if ($this->items instanceof Collection) {
            return new ArrayIterator($this->items->getIterator());
        }

        return new ArrayIterator($this->items);
    }

    /**
     * Get the number of items.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
