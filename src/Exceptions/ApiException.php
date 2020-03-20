<?php

namespace Api\Exceptions;

use Api\Exceptions\Contracts\ApiException as ApiExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class ApiException extends RuntimeException implements ApiExceptionInterface
{
    protected $status = 500;

    protected $payload;

    /**
     * ApiException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->payload = (new Payload())->message($message);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function data(array $data)
    {
        $this->payload->data($data);

        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function prepareResponse(ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write(
            $this->payload->toJson()
        );

        return $response->withStatus($this->status);
    }
}
