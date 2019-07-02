<?php

namespace Api\Representations;

use Api\Representations\Contracts\Representation as RepresentationContract;
use Api\Requests\Relation;
use Api\Requests\Request;
use Api\Support\Str;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Schema\Arr as ArrSchema;
use Neomerx\JsonApi\Wrappers\Arr;
use function utf8_encode;

/**
 * Class JsonApi
 * @package Api\Representations
 */
class JsonApi extends Representation implements RepresentationContract
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
        ])->withEncodeOptions(JSON_PRETTY_PRINT);
    }

    /**
     * @param Request $request
     * @param array $collection
     * @return mixed
     */
    public function forCollection(Request $request, array $collection)
    {
        $this->encoder->withIncludedPaths($this->collapseRelations($request->relations()));

        return $this->encoder->encodeCollectionArray(
            $request->segments()[count($request->segments()) - 1],
            $this->prepare($collection)
        );
    }

    /**
     * @param array $relations
     * @return array
     */
    public function collapseRelations(array $relations): array
    {
        $collapsed = [];

        foreach ($relations as $relation) {
            /** @var Relation $relation */
            if ($relation->getRelations()) {
                $collapsed = array_merge($collapsed, array_map(function ($subRelation) use ($relation) {
                    return Str::camel($relation->getName()) . '.' . $subRelation;
                }, $this->collapseRelations($relation->getRelations())));

                continue;
            }

            $collapsed[] = Str::camel($relation->getName());
        }

        return $collapsed;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    protected function prepare($data)
    {
        $data = $this->encodeUtf8($data);
        $data = $this->camelKeys($data);

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function encodeUtf8(array $data): array
    {
        return array_map(function ($datum) {
            return is_array($datum) ? $this->encodeUtf8($datum) : utf8_encode($datum);
        }, $data);
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
     * @param Request $request
     * @param array $item
     * @return mixed
     */
    public function forSingleton(Request $request, array $item)
    {
        $this->encoder->withIncludedPaths(['exhibitions']);

        return $this->encoder->encodeSingletonArray(
            'museums',
            $this->prepare($item)
        );
    }
}