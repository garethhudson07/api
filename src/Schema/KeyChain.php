<?php

namespace Api\Schema;

class KeyChain
{
    /**
     * @var Property|null
     */
    protected ?Property $primary = null;

    /**
     * @return Property|null
     */
    public function getPrimary(): ?Property
    {
        return $this->primary;
    }

    /**
     * @param Property $property
     * @return static
     */
    public function setPrimary(Property $property): static
    {
        $this->primary = $property;

        return $this;
    }
}
