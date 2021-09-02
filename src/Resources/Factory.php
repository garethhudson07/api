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
use Stitch\DBAL\Schema\Table;
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
    public function registry(): Registry
    {
        if (!$this->registry) {
            $this->registry = new Registry($this->kernel->getContainer(), $this);
        }

        return $this->registry;
    }

    /**
     * @param mixed ...$arguments
     * @return Collectable
     */
    public function collectable(...$arguments): Collectable
    {
        if ($arguments[0] instanceof Closure) {
            $schema = $this->stitchSchema($arguments[0]);
            $repository = new StitchRepository(
                new Model($schema->getTable())
            );
        } else {
            list($schema, $repository) = $arguments;
        }

        return new Collectable(
            $schema,
            $repository,
            $this->relationsFactory->registry($this->kernel->getContainer()),
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
    public function schema(Closure $callback): Schema
    {
        $schema = new Schema();

        $callback($schema);

        return $schema;
    }

    /**
     * @param Closure $callback
     * @return StitchSchema
     */
    public function stitchSchema(Closure $callback): StitchSchema
    {
        $schema = new StitchSchema(new Table());

        $callback($schema);

        return $schema;
    }
}
