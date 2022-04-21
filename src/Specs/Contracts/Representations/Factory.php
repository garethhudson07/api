<?php

namespace Api\Specs\Contracts\Representations;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface Representation
 * @package Api\Representations\Contracts
 */
interface Factory
{
    public function encoder(ServerRequestInterface $request);

    public function record(string $type);
}
