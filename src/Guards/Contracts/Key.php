<?php

namespace Api\Guards\Contracts;

use Api\Guards\OAuth2\Scopes\Collection;

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
     * @return array|null
     */
    public function getUser(): ?array;

    /**
     * @return string|int|null
     */
    public function getUserId();

    /**
     * @return Collection|null
     */
    public function getScopes(): ?Collection;
}
