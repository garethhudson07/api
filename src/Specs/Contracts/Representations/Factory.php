<?php

namespace Api\Specs\Contracts\Representations;

/**
 * Interface Representation
 * @package Api\Representations\Contracts
 */
interface Factory
{
    public function encoder();

    public function record(string $type);
}
