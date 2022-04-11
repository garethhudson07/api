<?php

namespace Api\Result\Contracts;

use ArrayAccess;
use Countable;
use IteratorAggregate;

interface Set extends ArrayAccess, IteratorAggregate, Countable
{
    public function transform(): array;
}
