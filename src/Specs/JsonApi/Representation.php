<?php

namespace Api\Specs\JsonApi;

use Api\Queries\Relation as RequestRelation;
use Api\Queries\Relations as RequestRelations;
use Api\Specs\Contracts\Representation as RepresentationContract;
use Api\Support\Str;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Schema\Arr as ArrSchema;
use Neomerx\JsonApi\Wrappers\Arr;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class JsonApi
 * @package Api\Representations
 */
class Representation implements RepresentationContract
{
    /**
     * @var Encoder
     */
    protected $encoder;

    /**
     * JsonApi constructor.
     */
    public function __construct()
    {
        $this->encoder = Encoder::instance([
            Arr::class => ArrSchema::class
        ])->withEncodeOptions(JSON_INVALID_UTF8_IGNORE);
    }

    /**
     * @param string $name
     * @param ServerRequestInterface $request
     * @param array $collection
     * @return string|null
     */
    public function forCollection(string $name, ServerRequestInterface $request, array $collection): ?string
    {
        if ($query = $request->getAttribute('query')) {
            $this->encoder->withIncludedPaths(
                $this->collapseRelations($query->relations())
            );
        }

        return $this->prepare($this->encoder->encodeCollectionArray($name, $collection));
    }

    /**
     * @param RequestRelations $relations
     * @return array
     */
    public function collapseRelations(RequestRelations $relations): array
    {
        $collapsed = [];

        foreach ($relations as $relation) {
            /** @var RequestRelation $relation */
            if ($relation->getRelations()->count()) {
                $collapsed = array_merge($collapsed, array_map(function ($subRelation) use ($relation) {
                    return $relation->getName() . '.' . $subRelation;
                }, $this->collapseRelations($relation->getRelations())));

                continue;
            }

            $collapsed[] = $relation->getName();
        }

        return $collapsed;
    }

    /**
     * @param mixed $data
     * @return string|null
     */
    protected function prepare($data): ?string
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        $data = $this->camelKeys($data);

        return json_encode($data) ?: null;
    }

    /**
     * @param array $data
     * @param bool $recursive
     * @return array
     */
    protected function camelKeys(array $data, bool $recursive = true): array
    {
        $camelCased = [];

        foreach ($data as $key => $value) {
            $key = Str::camel($key);

            if ($recursive && is_array($value)) {
                $value = static::camelKeys($value);
            }

            $camelCased[$key] = $value;
        }

        return $camelCased;
    }

    /**
     * @param string $name
     * @param ServerRequestInterface $request
     * @param array $item
     * @return string|null
     */
    public function forSingleton(string $name, ServerRequestInterface $request, array $item): ?string
    {
        if ($query = $request->getAttribute('query')) {
            $this->encoder->withIncludedPaths(
                $this->collapseRelations($query->relations())
            );
        }

        return $this->prepare($this->encoder->encodeSingletonArray($name, $item));
    }
}
