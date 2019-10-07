<?php

namespace Api\Specs\Contracts;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface Representation
 * @package Api\Representations\Contracts
 */
interface Representation
{
    /**
     * @param string $name
     * @param ServerRequestInterface $request
     * @param array $collection
     * @return mixed
     */
    public function forCollection(string $name, ServerRequestInterface $request, array $collection);

    /**
     * @param string $name
     * @param ServerRequestInterface $request
     * @param array $item
     * @return mixed
     */
    public function forSingleton(string $name, ServerRequestInterface $request, array $item);
}