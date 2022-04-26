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
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return Encoder
     */
    public function encoder(): Encoder
    {
        return new Encoder($this->request);
    }

    /**
     * @param string $type
     * @return Record
     */
    public function record(string $type): Record
    {
        return new Record($type);
    }
}
