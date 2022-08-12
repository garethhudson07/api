<?php

namespace Api\Queries;

use Api\Config\Manager;
use Api\Schema\Schema;
use Api\Specs\Contracts\Parser as ParserInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Resources\Resource;
use Api\Queries\Paths\Path;

class Query
{
    protected string $type = '';

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
    public function __construct(string $type)
    {
        $this->type = $type;
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
        $instance = new static($segments[count($segments) - 1] ?? '');

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
     * @param Resource $resource
     * @return $this
     */
    public function resolve(Resource $resource): static
    {
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
                        ->resolveProperties($foreignResource, $relation->getsort())
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
                $resource->getRelation($relation->getName())->getLocalResource(),
                $relation->getPath()
            );
        }

        return $resource->getSchema();
    }
}
