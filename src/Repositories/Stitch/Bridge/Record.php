<?php

namespace Api\Repositories\Stitch\Bridge;

use Api\Repositories\Contracts\Record as RecordInterface;
use Stitch\Result\Record as ResultRecord;

class Record implements RecordInterface
{
    protected $resultRecord;

    public function __construct(ResultRecord $resultRecord)
    {
        $this->resultRecord = $resultRecord;
    }

    public function getId()
    {

    }

    public function getAttributes($author): iterable
    {
        return $this->resultRecord->getData();
    }
}
