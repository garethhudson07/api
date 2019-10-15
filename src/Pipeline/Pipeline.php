<?php

namespace Api\Pipeline;

use Api\Http\Requests\Parser;
use Api\Resources\Registry;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Pipeline
 * @package Api\Pipeline
 */
class Pipeline
{
    protected $request;

    /**
     * @var array
     */
    protected $pipes = [];

    protected $prefix;

    /**
     * Pipeline constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function prefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function segments()
    {
        $segments = $this->request->getAttribute('segments');

        if ($this->prefix) {
            $segments = array_slice(
                $segments,
                count(Parser::segments($this->prefix))
            );
        }

        return $segments;
    }

    /**
     * @return Pipe
     */
    protected function makePipe()
    {
        return new Pipe($this, $this->request);
    }

    /**
     * @return Pipe
     */
    protected function newPipe()
    {
        $pipe = $this->makePipe();

        $this->pipes[] = $pipe;

        return $pipe;
    }

    /**
     * @param Registry $resources
     * @return Pipeline
     */
    public function assemble(Registry $resources)
    {
        /** @var Pipe $pipe */
        $pipe = null;

        foreach ($this->segments() as $segment) {
            if ($pipe && !$pipe->hasKey()) {
                if ($pipe->isCollectable()) {
                    $pipe->setKey(urldecode($segment));

                    continue;
                }
            }

            $pipe = $this->newPipe();

            if ($penultimate = $this->penultimate()) {
                $pipe->setEntity($penultimate->getResource()->getRelation($segment))->scope($penultimate);
            } else {
                $pipe->setEntity($resources->get($segment));
            }
        }

        return $this->classify();
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->pipes;
    }

    /**
     * @return mixed|null
     */
    public function last()
    {
        return $this->pipes[count($this->pipes) - 1] ?? null;
    }

    /**
     * @return mixed|null
     */
    public function penultimate()
    {
        return $this->pipes[count($this->pipes) - 2] ?? null;
    }

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function before(Pipe $pipe)
    {
        return array_slice($this->pipes, 0, array_search($pipe, $this->pipes));
    }

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function after(Pipe $pipe)
    {
        return array_slice($this->pipes, array_search($pipe, $this->pipes) + 1);
    }

    /**
     * @return $this
     */
    protected function classify()
    {
        foreach ($this->pipes as $pipe) {
            $pipe->classify();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function call()
    {
        foreach ($this->pipes as $pipe) {
            $pipe->call();
        }

        return $this;
    }
}
