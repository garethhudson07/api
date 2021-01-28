<?php

namespace Api\Guards\Contracts;

use Api\Pipeline\Pipeline;

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
     * @return mixed
     */
    public function getUser();

    /**
     * @return mixed
     */
    public function getUserId();
}
