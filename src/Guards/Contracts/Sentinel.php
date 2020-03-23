<?php

namespace Api\Guards\Contracts;

use Api\Pipeline\Pipeline;

interface Sentinel
{
    public function protect(Pipeline $pipeline);

    public function getUser();
}