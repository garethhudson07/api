<?php

namespace Api\Transformers\Contracts;

use Api\Result\Contracts\Record;

interface Transformer
{
    public function transform(Record $record): array;

    public function transformMetaData(Record $record): array;

    public function reverse(array $attributes): array;
}
