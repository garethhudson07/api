<?php

namespace Api\Specs\Contracts;

interface Encoder
{
    /**
     * @return mixed
     */
    public function getData(): mixed;

    /**
     * @param mixed $data
     * @return static
     */
    public function setData(mixed $data): static;

    /**
     * @param mixed $data
     * @return string
     */
    public function encode(mixed $data = null): string;

    /**
     * @return string
     */
    public function __toString(): string;
}
