<?php

namespace Api\Resources;

use League\Container\Container;
use Api\Resources\Relations\Factory as RelationsFactory;
use Api\Repositories\Stitch\Repository as StitchRepository;
use Api\Specs\Contracts\Representation;
use Closure;
use Stitch\Model;
use Stitch\Stitch;

class Factory
{
    protected $container;

    protected $relationsFactory;

    protected $registry;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->relationsFactory = new RelationsFactory($this->registry());
    }

    /**
     * @return Registry
     */
    public function registry()
    {
        if (!$this->registry) {
            $this->registry = new Registry($this);
        }

        return $this->registry;
    }

    /**
     * @param $value
     * @return Collectable
     */
    public function collectable($value)
    {
        return new Collectable(
            $value instanceof Model ? new StitchRepository($value) : $value,
            $this->relationsFactory->registry(),
            $this->container->get(Representation::class)
        );
    }

    /**
     * @param $value
     */
    public function singleton($value)
    {
    }

    /**
     * @param Closure $callback
     * @return Model
     */
    public function model(Closure $callback)
    {
        return Stitch::make($callback);
    }
}
