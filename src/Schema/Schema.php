<?php

namespace Api\Schema;

use Aggregate\Map;
use Api\Schema\Validation\Aggregate as ValidatorAggregate;
use Api\Schema\Validation\ValidationException;
use Api\Schema\Validation\Factory as ValidatorFactory;
use Closure;

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
     * @var Property[]
     */
    protected array $properties = [];

    /**
     * @var KeyChain
     */
    protected KeyChain $keyChain;

    public function __construct()
    {
        $this->keyChain = new KeyChain();
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param Map $input
     * @return bool
     * @throws ValidationException
     */
    public function validate(Map $input): bool
    {
        $validator = $this->resolveValidator();

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
     * @param $condition
     * @param Closure $callback
     * @return $this
     */
    public function when($condition, Closure $callback): self
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
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
     * @param string $name
     * @return Property|null
     */
    public function __get(string $name): ?Property
    {
        return $this->getProperty($name);
    }

    /**
     * @param Property $property
     * @return static
     */
    public function addProperty(Property $property): static
    {
        $this->properties[$property->getName()] = $property;

        return $this;
    }

    /**
     * @param string $name
     * @return static
     */
    public function removeProperty(string $name): static
    {
        if (array_key_exists($name, $this->properties)) {
            unset($this->properties[$name]);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @return Property
     */
    protected function makeProperty(string $name, string $type): Property
    {
        return (new Property(
            $name,
            $type,
            ValidatorFactory::make($type)
        ))->setSchema($this);
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
     * @return Property|null
     */
    public function getProperty(string $name): ?Property
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * @return bool
     */
    public function hasPrimary(): bool
    {
        return $this->keyChain->getPrimary() !== null;
    }

    /**
     * @return Property|null
     */
    public function getPrimary(): ?Property
    {
        return $this->keyChain->getPrimary();
    }

    /**
     * @return KeyChain
     */
    public function getKeyChain(): KeyChain
    {
        return $this->keyChain;
    }

    /**
     * @param KeyChain $keyChain
     * @return static
     */
    public function setKeyChain(KeyChain $keyChain): static
    {
        $this->keyChain = $keyChain;

        return $this;
    }
}
