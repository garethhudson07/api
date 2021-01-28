<?php

namespace Api\Guards\OAuth2\Scopes;

/**
 * Class Collection
 * @package Api\Guards\OAuth2\Scopes
 */
class Collection
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param array $scopes
     * @return $this
     */
    public function fill(array $scopes): self
    {
        foreach ($scopes as $scope) {
            $this->push($scope);
        }

        return $this;
    }

    /**
     * @param Scope $scope
     * @return $this
     */
    public function push(Scope $scope): self
    {
        $this->items[] = $scope;

        return $this;
    }

    /**
     * @param string $operation
     * @param string $resource
     * @return bool
     */
    public function can(string $operation, string $resource): bool
    {
        foreach ($this->items as $scope) {
            if ($scope->isFor($resource) && $scope->isAllowedTo($operation)) {
                return true;
            }
        }

        return false;
    }
}
