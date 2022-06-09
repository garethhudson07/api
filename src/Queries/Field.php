<?php

namespace Api\Queries;

use Api\Schema\Property;
use Api\Queries\Paths\Path;

/**
 * Class Order
 * @package Api\Http\Requests
 */
class Field
{
    protected Path $path;

    /**
     * @var string
     */
    protected string $propertyName = '';

    /**
     * Condition constructor.
     */
    public function __construct()
    {
        $this->path = new Path();
    }

    /**
     * @param Property $property
     * @return $this
     */
    public function setProperty(Property $property): static
    {
        $this->path->setEntity($property);

        return $this;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @param string $propertyName
     * @return $this
     */
    public function setPropertyName(string $propertyName): static
    {
        $this->propertyName = $propertyName;

        return $this;
    }

    /**
     * @return Path
     */
    public function getPath(): Path
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getPath();
    }
}
