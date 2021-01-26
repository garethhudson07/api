<?php

namespace Api\Guards\OAuth2\League\Entities;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use Stitch\Records\Record;

/**
 * Class Client
 * @package Api\Guards\OAuth2\League\Entities
 */
class Client implements ClientEntityInterface
{
    use EntityTrait, ClientTrait;

    /**
     * Client constructor.
     * @param Record $record
     * @noinspection PhpUndefinedFieldInspection
     */
    public function __construct(Record $record)
    {
        $this->isConfidential = true;
        $this->setIdentifier($record->id);

        $this->name = $record->name;
        $this->redirectUri = array_map(function ($item) {
            return $item->uri;
        }, $record->redirects->toArray());
    }
}
