<?php

namespace Api;

use Api\Config\Store as Config;
use Api\Specs\Factory as SpecFactory;
use Api\Guards\Factory as GuardFactory;
use Api\Http\Requests\Factory as RequestFactory;
use Api\Http\Responses\Factory as ResponseFactory;
use Api\Resources\Factory as ResourceFactory;
use Exception;
use Stitch\Stitch;
use Stitch\Model;
use Closure;

/**
 * Class Factory
 * @package Api
 */
class Factory
{
//    protected $config;
//
//    protected $spec;
//
//    protected $request;
//
//    protected $response;
//
//    protected $guard;

//    protected $resource;

//    /**
//     * Factory constructor.
//     * @param Config|null $config
//     */
//    public function __construct(?Config $config = null)
//    {
//        $this->config = $config;
//    }

    /**
     * @param string $name
     * @param Closure $callback
     * @return $this
     */
    public function configure(string $name, Closure $callback)
    {
        $this->config->configure($name, $callback);

        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     * @throws Exception
     */
    protected function getConfig(string $name)
    {
        if (!$this->config->has($name)) {
            throw new Exception("No config found for '$name'");
        }

        return $this->config->get($name);
    }

    /**
     * @return SpecFactory
     * @throws Exception
     */
    public function spec()
    {
        if (!$this->spec) {
            $this->spec = new SpecFactory($this->getConfig('specification'));
        }

        return $this->spec;
    }

    /**
     * @return RequestFactory
     * @throws Exception
     */
    public function request()
    {
        if (!$this->request) {
            $this->request = new RequestFactory($this->getConfig('specification'));
        }

        return $this->request;
    }

    /**
     * @return ResponseFactory
     */
    public function response()
    {
        if (!$this->response) {
            $this->response = new ResponseFactory();
        }

        return $this->response;
    }

    /**
     * @return GuardFactory
     * @throws Exception
     */
    public function guard()
    {
        if (!$this->guard) {
            $this->guard = new GuardFactory($this->getConfig('guard'));
        }

        return $this->guard;
    }

    public function resource()
    {
        if (!$this->resource) {
            $this->resource = new ResourceFactory($this);
        }

        return $this->resource;
    }

    /**
     * @param $value
     * @return Resources\Collectable
     */
    public function collectable($value)
    {
        return $this->resource()->collectable($value);
    }

    /**
     * @param Closure $callback
     * @return Model
     */
    public function model(Closure $callback)
    {
        return Stitch::make($callback);
    }
}
