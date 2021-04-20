<?php

namespace Api\Schema;

use Api\Schema\Validation\Aggregate as Validators;
use Api\Schema\Validation\ValidationException;
use Api\Schema\Validation\Validator;

/**
 * Class Schema
 * @package Api\Schema
 * @method Property string(string $name)
 * @method Property integer(string $name)
 * @method Property boolean(string $name)
 */
class Schema
{

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var Validators
     */
    protected $validators;

    /**
     * Schema constructor.
     */
    public function __construct()
    {
        $this->validators = new Validators();
    }

    /**
     * @param array $input
     * @return bool
     * @throws ValidationException
     */
    public function validate(array $input): bool
    {
        return $this->validators->run($input);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return Property
     */
    public function __call(string $method, array $arguments): Property
    {
        return $this->addProperty($method, $arguments[0]);
    }

    /**
     * @param string $type
     * @param string $name
     * @return Property
     */
    protected function addProperty(string $type, string $name): Property
    {
        $property = $this->makeProperty($name, $type);

        $this->properties[$name] = $property;
        $this->validators[$name] = $property->getValidator();

        return $property;
    }

    /**
     * @param string $name
     * @param string $type
     */
    protected function makeProperty(string $name, string $type)
    {
        return new Property(
            $name,
            $type,
            new Validator($type)
        );
    }
}
