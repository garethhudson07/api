<?php

namespace Api\Result\Contracts;

interface Collection
{
    /**
     * @return iterable
     */
    public function getItems(): iterable;

    /**
     * @return iterable
     */
    public function getMetaData(): iterable;
}
