<?php

namespace Api\Exceptions\Handlers;

use Api\Collection;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Aggregate extends Collection
{
    /**
     * @return Aggregate
     */
    public function extend(): Aggregate
    {
        return (new static())->fill($this->items);
    }

    /**
     * @param Throwable $exception
     * @param ResponseInterface $response
     * @return null|ResponseInterface
     */
    public function handle(Throwable $exception, ResponseInterface $response): ?ResponseInterface
    {
        foreach ($this->items as $item) {
            $exception = $item->process($exception);
        }

        if ($renderer = $this->renderer($exception)) {
            return $renderer->prepareResponse($exception, $response);
        }

        return null;
    }

    /**
     * @param Throwable $exception
     * @return null
     */
    public function renderer(Throwable $exception)
    {
        foreach ($this->items as $item) {
            if ($item->respondsTo($exception)) {
                return $item;
            }
        }

        return null;
    }
}