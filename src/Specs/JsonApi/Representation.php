<?php

namespace Api\specs\JsonApi;

use Api\Specs\Contracts\Representation as RepresentationContract;
use Api\Http\Requests\Relation as RequestRelation;
use Api\Http\Requests\Relations as RequestRelations;
use Api\Support\Str;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Schema\Arr as ArrSchema;
use Neomerx\JsonApi\Wrappers\Arr;
use Psr\Http\Message\ServerRequestInterface;
use function utf8_encode;

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
        ])->withEncodeOptions(JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE);
    }

    /**
     * @param string $name
     * @param ServerRequestInterface $request
     * @param array $collection
     * @return mixed|string
     */
    public function forCollection(string $name, ServerRequestInterface $request, array $collection)
    {
        $this->encoder->withIncludedPaths($this->collapseRelations($request->getAttribute('relations') ?? []));

        return $this->encoder->encodeCollectionArray(
            $name,
            $this->prepare($collection)
        );
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
            if (is_array($datum)) {
                return $this->encodeUtf8($datum);
            }

            if (!is_string($datum)) {
                return $datum;
            }

            return utf8_encode($datum);
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
     * @param string $name
     * @param ServerRequestInterface $request
     * @param array $item
     * @return mixed|string
     */
    public function forSingleton(string $name, ServerRequestInterface $request, array $item)
    {
        $this->encoder->withIncludedPaths($this->collapseRelations($request->getAttribute('relations') ?? []));

        return $this->encoder->encodeSingletonArray(
            $name,
            $this->prepare($item)
        );
    }
}