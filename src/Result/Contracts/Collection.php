<?php

namespace Api\Result\Contracts;

interface Collection
{
    public function getItems(): iterable;

    public function getMetaData(): iterable;
}
