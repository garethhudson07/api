<?php


namespace Api\Specs\JsonApi\Representations;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Schema\BaseSchema;

class Schema extends BaseSchema implements SchemaInterface
{
    /**
     * Get resource attributes.
     *
     * @param mixed            $resource
     * @param ContextInterface $context
     *
     * @return iterable
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $attributes = $resource->getAttributes();

        unset($attributes['id']);

        return $attributes;
    }

    /**
     * Get resource relationship descriptions.
     *
     * @param mixed            $resource
     * @param ContextInterface $context
     *
     * @return iterable
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        foreach ($resource->getRelations() as $key => $relation) {
            $relationships[$key] = [
                self::RELATIONSHIP_DATA => $relation,
            ];
        }

        return $relationships;
    }

    /**
     * Get resource type.
     *
     *
     * @return string
     */
    public function getType(): string
    {
        return 'any';
    }

    /**
     * Get resource identity. Newly created objects without ID may return `null` to exclude it from encoder output.
     *
     * @param object $resource
     *
     * @return string|null
     */
    public function getId($resource): ?string
    {
        return $resource->getAttribute('id');
    }

    /**
     * @inheritdoc
     */
    public function isAddSelfLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isAddRelatedLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getLinks($resource): iterable
    {
        return [];
    }
}
