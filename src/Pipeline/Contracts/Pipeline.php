<?php

namespace Api\Pipeline\Contracts;

use Psr\Http\Message\ResponseInterface;
use Api\Resources\Registry;
use Api\Pipeline\Pipes\Pipe;
use Api\Pipeline\Pipes\Aggregate as Pipes;

/**
 * Class Pipeline
 * @package Api\Pipeline
 */
interface Pipeline
{
    /**
     * @param string $prefix
     * @return $this
     */
    public function prefix(string $prefix): Pipeline;

    /**
     * @return Pipe
     */
    public function newPipe(): Pipe;

    /**
     * @param Registry $resources
     * @return Pipeline
     */
    public function assemble(Registry $resources): Pipeline;

    /**
     * @return Pipe|null
     */
    public function last(): Pipe|null;

    /**
     * @return Pipe|null
     */
    public function penultimate(): Pipe|null;

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function before(Pipe $pipe): array;

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function after(Pipe $pipe): array;

    /**
     * @return Pipes
     */
    public function all(): Pipes;

    /**
     * @return Pipeline
     */
    public function call(): Pipeline;

    /**
     * @return ResponseInterface
     */
    public function prepareResponse();
}
