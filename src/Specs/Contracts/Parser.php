<?php

namespace Api\Specs\Contracts;

use Api\Config\Manager;
use Api\Queries\Query;
use Psr\Http\Message\ServerRequestInterface;

interface Parser
{
    /**
     * @param Query $query
     * @return static
     */
    public function setQuery(Query $query): static;

    /**
     * @param ServerRequestInterface $request
     * @param Manager $config
     * @return void
     */
    public function parse(ServerRequestInterface $request, Manager $config): void;

    /**
     * @param array $input
     * @return array
     */
    public function attributes(array $input): array;

    /**
     * @param array $input
     * @return mixed
     */
    public function id(array $input): mixed;

    /**
     * @param array $input
     * @return mixed
     */
    public function type(array $input): mixed;
}
