<?php

class Query
{
    protected $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function with($relations)
    {
        foreach ($relations as $requestRelation) {
            $name = $requestRelation->getName();
            $relation = $resource->getRelation($name);
            $foreignResource = $relation->getForeignResource();

            echo 'adding relation ' . $relation->getStitchRelation()->getName() . ' to ' . $this->model->getTable()->getName();
            echo '<br>';
            var_dump($relation->getStitchRelation()->getName());

            $this->model->addRelation($relation->getStitchRelation());

            $foreignResource->getRepository()->addRelations(
                $foreignResource,
                $requestRelation->getRelations()
            );
        }

        return $this;
    }
}