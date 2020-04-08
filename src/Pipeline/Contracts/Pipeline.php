<?php

namespace Api\Pipeline\Contracts;

use Psr\Http\Message\ResponseInterface;
use Api\Resources\Registry;
use Api\Pipeline\Pipes\Pipe;

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
    public function prefix(string $prefix);

    /**
     * @param Registry $resources
     * @return Pipeline
     */
    public function assemble(Registry $resources);

    /**
     * @return mixed|null
     */
    public function last();

    /**
     * @return mixed|null
     */
    public function penultimate();

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function before(Pipe $pipe);

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function after(Pipe $pipe);

    /**
     * @return $this
     */
    public function call();

    /**
     * @return ResponseInterface
     */
    public function prepareResponse();
}
