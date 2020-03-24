<?php

namespace Api\Guards\Contracts;

interface Key
{
    /**
     * @return $this
     */
    public function handle();

    /**
     * @return null
     */
    public function getUser();

    /**
     * @return mixed
     */
    public function getScopes();
}
