<?php

namespace Api\Specs\JsonApi\Representations;

use Api\Specs\Contracts\Representations\Factory as Contract;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class JsonApi
 * @package Api\Representations
 */
class Factory implements Contract
{
    /**
     * @param ServerRequestInterface $request
     * @return Encoder
     */
    public function encoder(ServerRequestInterface $request)
    {
        return new Encoder($request);
    }

    public function record(string $type)
    {
        return new Record($type);

//        return $this->makeEncoder()->encodeData($collection);
    }

//    /**
//     * @param RequestRelations $relations
//     * @return array
//     */
//    public function collapseRelations(RequestRelations $relations): array
//    {
//        $collapsed = [];
//
//        foreach ($relations as $relation) {
//            /** @var RequestRelation $relation */
//            if ($relation->getRelations()->count()) {
//                $collapsed = array_merge($collapsed, array_map(function ($subRelation) use ($relation) {
//                    return $relation->getName() . '.' . $subRelation;
//                }, $this->collapseRelations($relation->getRelations())));
//
//                continue;
//            }
//
//            $collapsed[] = $relation->getName();
//        }
//
//        return $collapsed;
//    }

//    /**
//     * @param mixed $data
//     * @return string|null
//     */
//    protected function prepare($data): ?string
//    {
//        if (is_string($data)) {
//            $data = json_decode($data, true);
//        }
//
//        $data = $this->camelKeys($data);
//
//        return json_encode($data) ?: null;
//    }

//    /**
//     * @param array $data
//     * @param bool $recursive
//     * @return array
//     */
//    protected function camelKeys(array $data, bool $recursive = true): array
//    {
//        $camelCased = [];
//
//        foreach ($data as $key => $value) {
//            $key = Str::camel($key);
//
//            if ($recursive && is_array($value)) {
//                $value = static::camelKeys($value);
//            }
//
//            $camelCased[$key] = $value;
//        }
//
//        return $camelCased;
//    }

//    /**
//     * @param ResultRecord $item
//     * @return string|null
//     */
//    public function record(ResultRecord $item): ?string
//    {
////        if ($query = $request->getAttribute('query')) {
////            $this->encoder->withIncludedPaths(
////                $this->collapseRelations($query->relations())
////            );
////        }
////
////        return $this->prepare($this->encoder->encodeSingletonArray($name, $item));
//    }
}
