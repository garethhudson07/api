<?php

namespace Api\Result\Contracts;

use ArrayAccess;

interface Record extends ArrayAccess
{
    public function transform(): array;
}
