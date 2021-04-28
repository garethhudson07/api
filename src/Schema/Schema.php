<?php

namespace Api\Schema;

use Api\Schema\Validation\Aggregate as ValidatorAggregate;
use Api\Schema\Validation\ValidationException;
use Api\Schema\Validation\Factory as ValidatorFactory;

/**
 * Class Schema
 * @package Api\Schema
 * @method Property string(string $name)
 * @method Property integer(string $name)
 * @method Property boolean(string $name)
 * @method Property decimal(string $name)
 * @method Property nest(string $name)
 * @method Property collection(string $name)
 */
class Schema
{
    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $input
     * @return bool
     * @throws ValidationException
     */
    public function validate(array $input): bool
    {
        $validator = $this->resolveValidator();

        // TODO move camel casing to representation class
        if (!$validator->run($input)) {
            throw (new ValidationException('The supplied resource is invalid.'))->data([
                'errorMessages' => $validator->getMessages()
            ]);
        }

        return true;
    }

    /**
     * @return ValidatorAggregate
     */
    public function resolveValidator(): ValidatorAggregate
    {
        $validator = new ValidatorAggregate();

        foreach ($this->properties as $property) {
            $validator[$property->getName()] = $property->resolveValidator();
        }

        return $validator;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return Property
     */
    public function __call(string $method, array $arguments): Property
    {
        $type = $method;

        if ($method === 'nest') {
            $type = 'schema';
        }

        $property = $this->makeProperty($arguments[0], $type);

        $this->addProperty($property);

        return $property;
    }

    /**
     * @param $property
     * @return $this
     */
    public function addProperty($property): self
    {
        $this->properties[$property->getName()] = $property;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeProperty(string $name): self
    {
        if (array_key_exists($name, $this->properties)) {
            unset($this->properties[$name]);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     */
    protected function makeProperty(string $name, string $type)
    {
        return new Property(
            $name,
            ValidatorFactory::make($type)
        );
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty(string $name): bool
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getProperty(string $name)
    {
        return $this->properties[$name] ?? null;
    }
}
