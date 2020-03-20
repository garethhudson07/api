<?php

namespace Api\Repositories\Contracts;

use Api\Pipeline\Pipes\Pipe;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface Repository
 * @package Api\Repositories\Contracts
 */
interface Repository
{
    /**
     * @param Pipe $pipe
     * @return array
     */
    public function getByKey(Pipe $pipe): array;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return array
     */
    public function getCollection(Pipe $pipe, ServerRequestInterface $request): array;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return array
     */
    public function getRecord(Pipe $pipe, ServerRequestInterface $request): array;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return array
     */
    public function create(Pipe $pipe, ServerRequestInterface $request): array;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return array
     */
    public function update(Pipe $pipe, ServerRequestInterface $request): array;

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function delete(Pipe $pipe): array;
}
