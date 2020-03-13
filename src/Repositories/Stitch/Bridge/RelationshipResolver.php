<?php

namespace Api\Repositories\Stitch\Bridge;

use Stitch\Model;
use Api\Resources\Resource;
use Api\Queries\Relations as RequestRelations;

class RelationshipResolver
{
    public static function associate(Model $model, Resource $resource, RequestRelations $relations)
    {
        foreach ($relations as $requestRelation) {
            $relation = $resource->getRelation($requestRelation->getName());
            $foreignResource = $relation->getForeignResource();

            $model->addRelation($relation->getStitchRelation());

            static::associate(
                $model,
                $foreignResource,
                $requestRelation->getRelations()
            );
        }
    }
}
