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
     * @param string $input
     * @return Order
     */
    public static function parse(string $input)
    {
        $direction = 'ASC';
        $operator = substr($input, 0, 1);

        if ($operator == '-' || $operator == '+') {
            if ($operator == '-') {
                $direction = 'DESC';
            }

            $input = substr($input, 1);
        }

        return (new static())->setProperty($input)->setDirection($direction);
    }

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
