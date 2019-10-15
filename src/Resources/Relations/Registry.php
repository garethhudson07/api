<?php

namespace Api\Resources\Relations;

use Api\Registry as AbstractRegistry;
use Closure;

/**
 * Class Collection
 * @package Api\Resources\Relations
 */
class Registry extends AbstractRegistry
{
    protected $factory;

    /**
     * Registry constructor.
     * @param Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param mixed ...$arguments
     * @return Registry
     */
    public function has(...$arguments)
    {
        return $this->include('has', $arguments);
    }

    /**
     * @param mixed ...$arguments
     * @return Registry
     */
    public function belongsTo(...$arguments)
    {
        return $this->include('belongsTo', $arguments);
    }

    /**
     * @param mixed ...$arguments
     * @return Registry
     */
    public function nest(...$arguments)
    {
        return $this->include('relation', $arguments);
    }

    /**
     * @param string $factoryMethod
     * @param array $arguments
     * @return Registry
     */
    protected function include(string $factoryMethod, array $arguments)
    {
        $localResource = array_shift($arguments);
        $name = array_shift($arguments);

        return $this->bind(
            $name,
            function () use ($name, $factoryMethod, $localResource, $arguments)
            {
                $relation = $this->factory->{$factoryMethod}($localResource)->name($name);

                if (count($arguments) && $arguments[0] instanceof Closure) {
                    $arguments[0]($relation);
                }

                if (!$relation->getBinding()) {
                    $relation->bind($name);
                }

                return $relation->boot();
            }
        );
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function resolve(string $name)
    {
        if ($this->items[$name] instanceof Closure) {
            $this->items[$name] = $this->items[$name]();;
        }

        return $this->items[$name];
    }
}
