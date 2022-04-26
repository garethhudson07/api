<?php

namespace Api\Guards\Contracts;

use Api\Guards\OAuth2\Scopes\Collection;
use Api\Result\Contracts\Record;

/**
 * Interface Key
 * @package Api\Guards\Contracts
 */
interface Key
{
    /**
     * @return $this
     */
    public function handle(): self;

    /**
     * @return Record|null
     */
    public function getUser(): ?Record;

    /**
     * @return string|int|null
     */
    public function getUserId();

    /**
     * @return Collection|null
     */
    public function getScopes(): ?Collection;
}
