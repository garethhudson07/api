<?php

namespace Api\Exceptions;

use JsonSerializable;

class Payload implements JsonSerializable
{
    protected $message;

    protected $data = [];

    /**
     * @param string $message
     * @return $this
     */
    public function message(string $message)
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
    public function data(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
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
    public function toJson()
    {
        return json_encode($this);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
