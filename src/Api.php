<?php

namespace Api;

use Api\Exceptions\Handler as ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Api\Resources\Factory as ResourceFactory;
use Api\Providers\RequestServiceProvider;
use Api\Providers\ResponseServiceProvider;
use Api\Providers\GuardServiceProvider;
use Api\Providers\SpecServiceProvider;
use Api\Providers\PipelineServiceProvider;
use Closure;
use Exception;

/**
 * Class Api
 * @package Api
 */
class Api
{
    protected $kernel;

    protected $resources;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->resources = (new ResourceFactory($kernel))->registry();

        $this->registerServices();
    }

    /**
     * @return $this
     */
    protected function registerServices()
    {
        $this->kernel->addServiceProvider(
            new RequestServiceProvider($this->kernel->getConfig('request'), $this->kernel->getConfig('specification'))
        )->addServiceProvider(
            new ResponseServiceProvider()
        )->addServiceProvider(
            new GuardServiceProvider($this->kernel->getConfig('guard'))
        )->addServiceProvider(
            new SpecServiceProvider($this->kernel->getConfig('specification'))
        )->addServiceProvider(
            new PipelineServiceProvider()
        );

        return $this;
    }

    /**
     * @param string $name
     * @param Closure $callback
     * @return $this
     */
    public function configure(string $name, Closure $callback)
    {
        $this->kernel->configure($name, $callback);

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function bind(...$arguments)
    {
        $this->kernel->bind(...$arguments);

        return $this;
    }

    /**
     * @param mixed ...$arguments
     * @return $this
     */
    public function listen(...$arguments)
    {
        $this->kernel->listen(...$arguments);

        return $this;
    }

    /**
     * @param string $name
     * @param Closure $callback
     */
    public function register(string $name, Closure $callback)
    {
        $this->resources->bind($name, $callback);
    }

    /**
     * @throws Exception
     */
    public function authorise()
    {
        $this->emitResponse($this->generateAuthorisationResponse());
    }

    /**
     * @return mixed|ResponseInterface
     * @throws Exception
     */
    public function generateAuthorisationResponse()
    {
        return $this->try(function ()
        {
            $authoriser = $this->kernel->resolve('guard.authoriser');

            if ($authoriser) {
                return $authoriser->authoriseAndPrepareResponse(
                    $this->kernel->resolve('response.factory')->make()
                );
            }

            return false;
        });
    }

    /**
     * @throws Exception
     */
    public function authorisedUser()
    {
        $this->emitResponse($this->generateAuthorisedUserResponse());
    }

    /**
     * @throws Exception
     */
    public function generateAuthorisedUserResponse()
    {
        $key = $this->kernel->resolve('guard.key');

        if ($key) {
            $key->handle();
            $user = $key->getUser();

            unset($user['password']);

            return $this->kernel->resolve('response.factory')->json(
                $this->kernel->resolve('representation')->forSingleton(
                    'user',
                    $this->kernel->resolve('request.factory')->instance(),
                    $user
                )
            );
        }

        return false;
    }

    /**
     * @param Closure $callback
     * @return mixed|ResponseInterface
     * @throws Exception
     */
    protected function try(Closure $callback)
    {
        try {
            return $callback();
        } catch (Exception $e) {
            return (new ExceptionHandler(
                $this->kernel->resolve('response.factory')->json()
            ))->handle($e);
        }
    }

    /**
     * @return mixed|ResponseInterface
     * @throws Exception
     */
    public function generateResponse()
    {
        return $this->try(function ()
        {
            $pipeline = $this->kernel->resolve('pipeline');

            if($prefix = $this->kernel->getConfig('request')->prefix) {
                $pipeline->prefix($prefix);
            }

            $pipeline->assemble($this->resources);

            if ($sentinel = $this->kernel->resolve('guard.sentinel')) {
                $sentinel->protect($pipeline);
            }

            return $pipeline->prepareResponse();
        });
    }

    /**
     * @param ResponseInterface $response
     */
    protected function emitResponse(ResponseInterface $response)
    {
        $this->kernel->resolve('response.factory')->emitter()->emit($response);
    }

    /**
     * @throws Exception
     */
    public function respond()
    {
        $this->emitResponse($this->generateResponse());
    }
}
