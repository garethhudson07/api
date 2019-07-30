<?php

namespace Api\specs\JsonApi;

use Api\Pipeline\Pipe;
use Api\Specs\Contracts\Representation as RepresentationContract;
use Api\Requests\Relation;
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
        ])->withEncodeOptions(JSON_PRETTY_PRINT);
    }

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @param array $collection
     * @return array|mixed|string
     */
    public function forCollection(Pipe $pipe, ServerRequestInterface $request, array $collection)
    {
        $this->encoder->withIncludedPaths($this->collapseRelations($request->getAttribute('relations') ?? []));

        return $this->encoder->encodeCollectionArray(
            $pipe->getEntity()->getName(),
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
            return is_array($datum) ? $this->encodeUtf8($datum) : (is_string($datum) ? utf8_encode($datum) : $datum);
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
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @param array $item
     * @return array|mixed|string
     */
    public function forSingleton(Pipe $pipe, ServerRequestInterface $request, array $item)
    {
        $this->encoder->withIncludedPaths($this->collapseRelations($request->getAttribute('relations') ?? []));

        return $this->encoder->encodeSingletonArray(
            $pipe->getEntity()->getName(),
            $this->prepare($item)
        );
    }
}