<?php

namespace Api\Repositories\Contracts;

use Api\Pipeline\Pipes\Pipe;
use Api\Result\Contracts\Collection as ResultCollectionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Api\Result\Contracts\Record as ResultRecordInterface;

/**
 * Interface Repository
 * @package Api\Repositories\Contracts
 */
interface Resource
{
    /**
     * @param Pipe $pipe
     * @return ResultRecordInterface|null
     */
    public function getByKey(Pipe $pipe): ?ResultRecordInterface;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return iterable
     */
    public function getCollection(Pipe $pipe, ServerRequestInterface $request): ResultCollectionInterface;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return ResultRecordInterface|null
     */
    public function getRecord(Pipe $pipe, ServerRequestInterface $request): ?ResultRecordInterface;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return ResultRecordInterface
     */
    public function create(Pipe $pipe, ServerRequestInterface $request): ResultRecordInterface;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return ResultRecordInterface
     */
    public function update(Pipe $pipe, ServerRequestInterface $request): ResultRecordInterface;

    /**
     * @param Pipe $pipe
     * @return ResultRecordInterface
     */
    public function delete(Pipe $pipe): ResultRecordInterface;
}
