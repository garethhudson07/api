<?php

namespace Api\Repositories\Stitch\Bridge;

use Aggregate\Contracts\Arrayable;
use Stitch\Result\Record as ResultRecord;

class Record implements Arrayable
{
    /**
     * @var ResultRecord
     */
    protected $resultRecord;

    public function __construct(ResultRecord $resultRecord)
    {
        $this->resultRecord = $resultRecord;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->resultRecord->toArray();
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->resultRecord->toArray();
    }
}
