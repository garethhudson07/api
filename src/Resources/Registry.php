<?php

namespace Api\Resources;

use Api\Registries\Binding;
use Api\Registries\Registry as BaseRegistry;
use Api\Container;
use Closure;

class Registry extends BaseRegistry
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
     * @param Binding $binding
     * @return mixed
     */
    public function resolve(Binding $binding)
    {
        $value = $binding->getValue();

        if ($value instanceof Closure) {
            return $value($this->factory)->name($binding->getKey());
        }

        return parent::resolve($binding);
    }
}
