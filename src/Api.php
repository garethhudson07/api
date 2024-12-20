<?php

namespace Api;

use Psr\Http\Message\ResponseInterface;
use Api\Resources\Factory as ResourceFactory;
use Api\Providers\RequestServiceProvider;
use Api\Providers\ResponseServiceProvider;
use Api\Providers\GuardServiceProvider;
use Api\Providers\SpecServiceProvider;
use Api\Providers\PipelineServiceProvider;
use Closure;
use Throwable;

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
     * @return Kernel
     */
    public function getKernel(): Kernel
    {
        return $this->kernel;
    }

    /**
     * @return $this
     */
    protected function registerServices()
    {
        $this->kernel->addServiceProvider(
            new RequestServiceProvider(
                $this->kernel->getConfig('request'),
                $this->kernel->getConfig('specification')
            )
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
     * @param mixed ...$arguments
     * @return $this
     */
    public function exceptionHandler(...$arguments)
    {
        $this->kernel->exceptionHandler(...$arguments);

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
     * @param string $name
     * @return mixed|null
     */
    public function resolve(string $name)
    {
        return $this->resources->get($name);
    }

    /**
     * @throws Throwable
     */
    public function authorise()
    {
        $this->emitResponse($this->generateAuthorisationResponse());
    }

    /**
     * @return mixed|ResponseInterface
     * @throws Throwable
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
     * @throws Throwable
     */
    public function revokeAuthorisation()
    {
        $this->emitResponse($this->generateRevokeAuthorisationResponse());
    }

    /**
     * @return mixed|ResponseInterface
     * @throws Throwable
     */
    public function generateRevokeAuthorisationResponse()
    {
        return $this->try(function ()
        {
            $authoriser = $this->kernel->resolve('guard.authoriser');

            if ($authoriser) {
                return $authoriser->revokeAuthorisationAndPrepareResponse(
                    $this->kernel->resolve('response.factory')->make()
                );
            }

            return false;
        });
    }

    /**
     * @throws Throwable
     */
    public function authorisedUser()
    {
        $this->emitResponse($this->generateAuthorisedUserResponse());
    }

    /**
     * @throws Throwable
     */
    public function generateAuthorisedUserResponse()
    {
        return $this->try(function ()
        {
            $key = $this->kernel->resolve('guard.key');

            if ($key) {
                $key->handle();
                $user = $key->getUser()?->getAttributes();

                if (!$user) {
                    return false;
                }

                $representationFactory = $this->kernel->resolve('representationFactory');

                unset($user['password']);

                return $this->kernel->resolve('response.factory')->json(
                    $representationFactory->encoder()->encode(
                        $representationFactory->record('user')->setAttributes($user)
                    )
                );
            }

            return false;
        });
    }

    /**
     * @param Closure $callback
     * @return mixed|ResponseInterface
     * @throws \Throwable
     */
    public function try(Closure $callback)
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            return $this->kernel->handleException($exception);
        }
    }

    /**
     * @return mixed|ResponseInterface
     * @throws Throwable
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
     * @throws Throwable
     */
    public function respond()
    {
        $this->emitResponse($this->generateResponse());
    }
}
