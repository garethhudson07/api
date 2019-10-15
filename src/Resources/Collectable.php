<?php

namespace Api\Resources;
use Api\Repositories\Contracts\Repository;
use Api\Resources\Relations\Registry as Relations;
use Api\Specs\Contracts\Representation;

/**
 * Class Collection
 * @package Api\Resources
 */
class Collectable extends Resource
{
    public function __construct(Repository $repository, Relations $relations, Representation $representation)
    {
        parent::__construct($repository, $relations, $representation);

        $this->endpoints->add(
            'index',
            'show',
            'create',
            'update',
            'destroy'
        );
    }
}
