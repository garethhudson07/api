<?php


namespace Api\Specs\JsonApi\Representations;

use Neomerx\JsonApi\Encoder\Encoder as BaseEncoder;
use Psr\Http\Message\ServerRequestInterface;
use Api\Queries\Relations as RequestRelations;


class Encoder
{
    protected $baseEncoder;

    public function __construct(ServerRequestInterface $request)
    {
        $this->baseEncoder = BaseEncoder::instance([
            Record::class => Schema::class
        ])->withIncludedPaths(
            $this->collapseRelations($request->getAttribute('query')->relations())
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
                    return "{$relation->getName()}.$subRelation";
                }, $this->collapseRelations($relation->getRelations())));

                continue;
            }

            $collapsed[] = $relation->getName();
        }

        return $collapsed;
    }

    public function encode($data)
    {
        return $this->baseEncoder->encodeData($data);
    }
}
