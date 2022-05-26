<?php

namespace Api\Queries;

use Api\Config\Manager;
use Api\Schema\Schema;
use Api\Specs\Contracts\Parser as ParserInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Resources\Resource;

class Query
{
    protected string $type = '';

    protected Relations $relations;

    protected array $fields = [];

    protected Expression $filters;

    protected array $sort = [];

    protected $limit;

    protected $offset;

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
    public function relations(): Relations
    {
        return $this->relations;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return array
     */
    public function fields(): array
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
    public function filters(): Expression
    {
        return $this->filters;
    }

    /**
     * @param array $sort
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
    public function sort(): array
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
    public function limit(): ?int
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
     * @return int|null
     */
    public function offset(): ?int
    {
        return $this->offset;
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
    public function search(): ?string
    {
        return $this->search;
    }

    /**
     * @param Resource $resource
     * @return $this
     */
    public function resolve(Resource $resource): static
    {
        return $this->resolveExpressionProperties($resource, $this->filters)
            ->resolveRelationResources($resource);
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
                $constraint->setProperty(
                    $this->getSchemaByPath($resource, $constraint->getPath())->getProperty(
                        $constraint->getPropertyName()
                    )
                );
            }
        }

        return $this;
    }

    /**
     * @param Resource $resource
     * @param string $path
     * @return Schema
     */
    protected function getSchemaByPath(Resource $resource, string $path): Schema
    {
        $pieces = explode('.', $path);

        if (count($pieces) === 1) {
            return $resource->getSchema();
        }

        $relation = array_shift($pieces);

        return $this->getSchemaByPath(
            $resource->getRelation($relation)->getLocalResource(),
            implode('.', $pieces)
        );
    }

    /**
     * @param Resource $resource
     * @return $this
     */
    protected function resolveRelationResources(Resource $resource): static
    {
        foreach ($this->relations as $relation) {
            $relation->setResource(
                $resource->getRelation(
                    $relation->getName()
                )->getLocalResource()
            );
        }

        return $this;
    }
}
