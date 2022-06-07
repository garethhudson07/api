<?php


namespace Api\Specs\JsonApi\Representations;

use Neomerx\JsonApi\Encoder\Encoder as BaseEncoder;
use Psr\Http\Message\ServerRequestInterface;
use Api\Queries\Relations as RequestRelations;
use Api\Specs\Contracts\Encoder as EncoderContract;
use Api\Support\Str;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;

class Encoder implements EncoderContract
{
    /**
     * @var EncoderInterface
     */
    protected EncoderInterface $baseEncoder;

    /**
     * @var mixed
     */
    protected $data = null;

    /**
     * @param ServerRequestInterface $request
     * @param mixed $data
     */
    public function __construct(ServerRequestInterface $request, mixed $data = null)
    {
        $this->data = $data;

        $this->baseEncoder = BaseEncoder::instance([
            Record::class => Schema::class
        ])->withIncludedPaths(
            $this->collapseRelations($request->getAttribute('parsedQuery')->getRelations())
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
     * @param mixed $data
     * @return string
     */
    public function encode(mixed $data = null): string
    {
        return $this->baseEncoder->encodeData($data ?? $this->data);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->encode($this->data);
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return static
     */
    public function setData(mixed $data): static
    {
        $this->data = $data;

        return $this;
    }
}
