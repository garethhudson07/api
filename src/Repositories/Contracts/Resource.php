<?php

namespace Api\Repositories\Contracts;

use Api\Pipeline\Pipes\Pipe;
use Psr\Http\Message\ServerRequestInterface;
use Api\Result\Stitch\Record as ResultRecord;
use Api\Result\Stitch\Set as ResultSet;

/**
 * Interface Repository
 * @package Api\Repositories\Contracts
 */
interface Resource
{
    /**
     * @param Pipe $pipe
     * @return ResultRecord|null
     */
    public function getByKey(Pipe $pipe): ?ResultRecord;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return ResultSet
     */
    public function getCollection(Pipe $pipe, ServerRequestInterface $request): ResultSet;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return ResultRecord|null
     */
    public function getRecord(Pipe $pipe, ServerRequestInterface $request): ?ResultRecord;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return ResultRecord
     */
    public function create(Pipe $pipe, ServerRequestInterface $request): ResultRecord;

    /**
     * @param Pipe $pipe
     * @param ServerRequestInterface $request
     * @return ResultRecord
     */
    public function update(Pipe $pipe, ServerRequestInterface $request): ResultRecord;

    /**
     * @param Pipe $pipe
     * @return ResultRecord
     */
    public function delete(Pipe $pipe): ResultRecord;
}
