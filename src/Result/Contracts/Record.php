<?php

namespace Api\Result\Contracts;

interface Record
{
    public function getRelations(): iterable;

    public function getAttributes(): array;

    public function getAttribute(string $key): mixed;
}
