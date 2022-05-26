<?php


namespace Api\Specs\JsonApi\Representations;

use Neomerx\JsonApi\Encoder\Encoder as BaseEncoder;
use Psr\Http\Message\ServerRequestInterface;
use Api\Queries\Relations as RequestRelations;
use Api\Support\Str;

class Encoder
{
    protected $baseEncoder;

    public function __construct(ServerRequestInterface $request)
    {
        $this->baseEncoder = BaseEncoder::instance([
            Record::class => Schema::class
        ])->withIncludedPaths(
            $this->collapseRelations($request->getAttribute('parsedQuery')->relations())
        );
    }

    /**
     * @param RequestRelations $relations
     * @return array
     */
    protected function collapseRelations(RequestRelations $relations): array
    {
        $collapsed = [];

        foreach ($relations as $relation) {
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
     * @param $data
     * @return string
     */
    public function encode($data): string
    {
        return $this->baseEncoder->encodeData($data);
    }
}
