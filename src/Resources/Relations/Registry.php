<?php

namespace Api\Resources\Relations;

use Api\Registry as AbstractRegistry;
use Api\Container;
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
     * @param Container $container
     * @param Factory $factory
     */
    public function __construct(Container $container, Factory $factory)
    {
        parent::__construct($container);

        $this->factory = $factory;
    }

    /**
     * @param string $factoryMethod
     * @param array $arguments
     * @return Registry
     */
    public function include(string $factoryMethod, array $arguments)
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
        $item = $this->bindings[$name];

        if ($item instanceof Closure) {
            $this->items[$name] = $item();

            return $this->items[$name];
        }

        return parent::resolve($name);
    }
}
