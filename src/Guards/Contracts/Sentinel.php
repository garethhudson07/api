<?php

namespace Api\Guards\Contracts;

use Api\Pipeline\Pipeline;
use Api\Result\Contracts\Record;

/**
 * Interface Sentinel
 * @package Api\Guards\Contracts
 */
interface Sentinel
{
    /**
     * @param Pipeline $pipeline
     * @return $this
     */
    public function protect(Pipeline $pipeline): Sentinel;

    /**
     * @return Record|null
     */
    public function getUser(): ?Record;

    /**
     * @return mixed
     */
    public function getUserId();
}
