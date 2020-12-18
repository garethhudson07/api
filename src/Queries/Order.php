<?php

namespace Api\Queries;

/**
 * Class Order
 * @package Api\Http\Requests
 */
class Order
{
    /**
     * @var
     */
    protected $property;

    /**
     * @var
     */
    protected $direction;

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @param string $property
     * @return $this
     */
    public function setProperty(string $property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function setDirection(string $direction)
    {
        $this->direction = $direction;

        return $this;
    }
}
