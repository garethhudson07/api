<?php

namespace Api\Exceptions;

use JsonSerializable;

/**
 * Class Payload
 * @package Api\Exceptions
 */
class Payload implements JsonSerializable
{
    /**
     * @var
     */
    protected $message;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param string $message
     * @return $this
     */
    public function message(string $message): Payload
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function data(array $data): Payload
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'errors' => [
                'title' => $this->message,
                'meta' => $this->data
            ]
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this);
    }
}
