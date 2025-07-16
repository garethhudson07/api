<?php

namespace Api\Queries;

use Api\Config\Manager;
use Api\Schema\Schema;
use Api\Specs\Contracts\Parser as ParserInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Resources\Resource;
use Api\Queries\Paths\Path;
use Api\Resources\Relations\Relation as ResourceRelation;
use Api\Resources\Relations\Stitch\Has;

class Query
{
    protected string $type = '';

    protected mixed $id = null;

    protected Relations $relations;

    protected array $fields = [];

    protected Expression $filters;

    protected array $sort = [];

    protected $limit;

    protected $offset;

    protected $page;

    protected $search;

    /**
     * Query constructor.
     * @param string $type
     */
    public function __construct(string $type, mixed $id = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->relations = new Relations();
        $this->filters = new Expression();
    }

    /**
     * @param ParserInterface $parser
     * @param ServerRequestInterface $request
     * @param Manager $config
     * @return Query
     */
    public static function extract(ParserInterface $parser, ServerRequestInterface $request, Manager $config): Query
    {
        $segments = $request->getAttribute('segments');

        if (empty($segments)) {
            throw new \InvalidArgumentException('No segments found in the request.');
        }

        // If segments is an even number, we assume the query is scoped to an id and the last segment is the id.
        // If segments is an odd number, we assume the query is for a collection and the last segment is the type.
        $type = count($segments) % 2 === 0 ? $segments[count($segments) - 2] : $segments[count($segments) - 1];
        $id = count($segments) % 2 === 0 ? $segments[count($segments) - 1] : null;

        $instance = new static($type, $id);

        $parser->setQuery($instance)->parse($request, $config);

        return $instance;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * @param Relation $relation
     * @return $this
     */
    public function addRelation(Relation $relation): static
    {
        $this->relations->push($relation);

        return $this;
    }

    /**
     * @return Relations
     */
    public function getRelations(): Relations
    {
        return $this->relations;
    }

    /**
     * @param Field $field
     * @return $this
     */
    public function addField(Field $field): static
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param $filters
     * @return $this
     */
    public function setFilters($filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return Expression
     */
    public function getFilters(): Expression
    {
        return $this->filters;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function addOrder(Order $order): static
    {
        $this->sort[] = $order;

        return $this;
    }

    /**
     * @param $sort
     * @return $this
     */
    public function setSort(array $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return array
     */
    public function getSort(): array
    {
        return $this->sort;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function setOffset($offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param $page
     * @return $this
     */
    public function setPage($page): static
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param $search
     * @return $this
     */
    public function setSearch($search): static
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }

    /**
     * @param Resource|ResourceRelation $resource
     * @return $this
     */
    public function resolve(Resource|ResourceRelation $resource): static
    {
        if ($resource instanceof Has) {
            $resource = $resource->getForeignResource();
        }

        if ($resource instanceof ResourceRelation) {
            $resource = $resource->getLocalResource();
        }

        return $this->resolveProperties($resource, $this->fields)
            ->resolveProperties($resource, $this->sort)
            ->resolveRelations($resource, $this->relations)
            ->resolveExpressionProperties($resource, $this->filters);
    }

    /**
     * @param Resource $resource
     * @param array $items
     * @return $this
     */
    protected function resolveProperties(Resource $resource, array $items): static
    {
        foreach ($items as $item) {
            $property = $this->getSchemaByPath($resource, $item->getPath())->getProperty(
                $item->getPropertyName()
            );

            if ($property) {
                $item->setProperty($property);
            }
        }

        return $this;
    }

    /**
     * @param Resource $resource
     * @param Expression $expression
     * @return $this
     */
    protected function resolveExpressionProperties(Resource $resource, Expression $expression): static
    {
        foreach ($expression->getItems() as $item) {
            $constraint = $item['constraint'];

            if ($constraint instanceof Expression) {
                $this->resolveExpressionProperties($resource, $constraint);
            } else {
                $property = $this->getSchemaByPath($resource, $constraint->getPath())->getProperty(
                    $constraint->getPropertyName()
                );

                if ($property) {
                    $constraint->setProperty($property);
                }
            }
        }

        return $this;
    }

    /**
     * @param Resource $resource
     * @param Relations $relations
     * @return $this
     */
    protected function resolveRelations(Resource $resource, Relations $relations): static
    {
        foreach ($relations as $relation) {
            if ($resourceRelation = $resource->getRelation($relation->getName())) {
                if ($foreignResource = $resourceRelation->getForeignResource()) {
                    $relation->setResource($resourceRelation);
                    $this->resolveProperties($foreignResource, $relation->getFields())
                        ->resolveProperties($foreignResource, $relation->getSort())
                        ->resolveRelations($foreignResource, $relation->getRelations());
                }
            }
        }

        return $this;
    }

    /**
     * @param Resource $resource
     * @param Path $path
     * @return Schema
     */
    protected function getSchemaByPath(Resource $resource, Path $path): Schema
    {
        if ($relation = $path->getRelation()) {
            return $this->getSchemaByPath(
                $resource->getRelation($relation->getName())->getForeignResource(),
                $relation->getPath()
            );
        }

        return $resource->getSchema();
    }
}
