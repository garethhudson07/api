<?php

namespace Api\Specs\Contracts\Representations;

interface Record
{
    public function getType(): string;

    public function getAttribute(string $key): mixed;

    public function getAttributes(): array;

    public function setAttributes(array $attributes): static;

    public function getRelations(): array;

    public function addRelation(string $name, mixed $relation): static;

    public function getMetaData(string $key): mixed;

    public function getMeta(): array;

    public function setMetaData(string $key, mixed $value): static;

    public function setMeta(array $meta): static;
}
