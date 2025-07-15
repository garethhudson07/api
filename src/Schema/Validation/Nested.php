<?php

namespace Api\Schema\Validation;

abstract class Nested
{
    protected $baseValidator;

    protected $childValidator;

    protected $messages = [];

    public function __construct($baseValidator)
    {
        $this->baseValidator = $baseValidator;
    }

    public function isSometimes(): bool
    {
        return $this->baseValidator->isSometimes();
    }

    public function hasRule(string $name): bool
    {
        return $this->baseValidator->hasRule($name);
    }

    public function hasHook(string $name): bool
    {
        return $this->baseValidator->hasHook($name);
    }

    /**
     * @param $validator
     * @return $this
     */
    public function childValidator($validator): self
    {
        $this->childValidator = $validator;

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return $this
     */
    public function clearMessages(): self
    {
        $this->messages = [];

        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return self
     */
    public function __call($name, $arguments): self
    {
        if ($this->baseValidator->hasRule($name)) {
            $this->baseValidator->{$name}(...$arguments);
        }

        return $this;
    }

    /**
     * @param mixed $input
     * @return bool
     */
    abstract public function run(mixed $input): bool;
}
