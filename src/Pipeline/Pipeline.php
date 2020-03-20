<?php

namespace Api\Pipeline;

use Api\Pipeline\Pipes\Pipe;
use Api\Pipeline\Pipes\Aggregate as Pipes;
use Api\Http\Requests\Parser;
use Api\Resources\Registry;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Pipeline
 * @package Api\Pipeline
 */
class Pipeline
{
    protected $request;

    protected $response;

    protected $pipes;

    protected $prefix;

    /**
     * Pipeline constructor.
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->pipes = new Pipes();
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

        $this->pipes->push($pipe);

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
     * @return mixed|null
     */
    public function last()
    {
        return $this->pipes->last();
    }

    /**
     * @return mixed|null
     */
    public function penultimate()
    {
        return $this->pipes->penultimate();
    }

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function before(Pipe $pipe)
    {
        return $this->pipes->before($pipe);
    }

    /**
     * @param Pipe $pipe
     * @return array
     */
    public function after(Pipe $pipe)
    {
        return $this->pipes->after($pipe);
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

    /**
     * @return ResponseInterface
     */
    public function prepareResponse()
    {
        $pipe = $this->call()->last();
        $data = $pipe->getData();

        if ($data) {
            $this->response->getBody()->write($data);
        }

        switch ($pipe->getOperation()) {
            case 'create':
                $status = '201';
                break;

            case 'delete':
                $status = '204';
                break;

            default:
                $status = '200';
        }

        return $this->response->withStatus($status);
    }
}
