<?php

namespace Api\Exceptions;

use Api\Exceptions\Contracts\ApiException as ApiExceptionInterface;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class ApiException
 * @package Api\Exceptions
 */
class ApiException extends Exception implements ApiExceptionInterface
{
    /**
     * @var int
     */
    protected $status = 500;

    /**
     * @var Payload
     */
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
     * @return Payload
     */
    public function getPayload(): Payload
    {
        return $this->payload;
    }

    /**
     * @param array $data
     * @return self
     */
    public function data(array $data): self
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
        $response->getBody()->write((string) $this->payload->toJson());

        return $response->withStatus($this->status);
    }
}
