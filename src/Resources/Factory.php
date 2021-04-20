<?php

namespace Api\Resources;

use Api\Repositories\Contracts\Resource as RepositoryContract;
use Api\Schema\Schema;
use Api\Schema\Stitch\Schema as StitchSchema;
use Api\Kernel;
use Api\Resources\Relations\Factory as RelationsFactory;
use Api\Repositories\Stitch\Resource as StitchRepository;
use Api\Specs\Contracts\Representation;
use Closure;
use Stitch\Model;

class Factory
{
    protected $kernel;

    protected $relationsFactory;

    protected $registry;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->relationsFactory = new RelationsFactory($this);
    }

    /**
     * @return Registry
     */
    public function registry()
    {
        if (!$this->registry) {
            $this->registry = new Registry($this->kernel->getContainer(), $this);
        }

        return $this->registry;
    }

    /**
     * @param Closure $callback
     * @param RepositoryContract|null $repository
     * @return Collectable
     */
    public function collectable(Closure $callback, ?RepositoryContract $repository = null): Collectable
    {
        $container = $this->kernel->getContainer();

        if ($repository) {
            $schema = $this->schema($callback);
        } else {
            $schema = $this->stitchSchema($callback);

            $repository = new StitchRepository(
                new Model($schema->getTable())
            );
        }

        return new Collectable(
            $schema,
            $repository,
            $this->relationsFactory->registry($container),
            $this->kernel->resolve(Representation::class),
            $this->kernel->getEmitter()->extend()
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
     * @return Schema
     */
    protected function schema(Closure $callback): Schema
    {
        $schema = new Schema();

        $callback($schema);

        return $schema;
    }

    /**
     * @param Closure $callback
     * @return StitchSchema
     */
    protected function stitchSchema(Closure $callback): StitchSchema
    {
        $schema = new StitchSchema();

        $callback($schema);

        return $schema;
    }
}
