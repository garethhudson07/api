<?php

namespace Api\Transformers;

use Api\Result\Contracts\Record;
use Api\Transformers\Contracts\Transformer as Contract;

class Transformer implements Contract
{
    /**
     * @param Record $record
     * @return array
     */
    public function transform(Record $record): array
    {
        return $record->getAttributes();
    }

    /**
     * @param Record $record
     * @return array
     */
    public function transformMetaData(Record $record): array
    {
        return [];
    }
}
