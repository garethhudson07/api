<?php

namespace Api\Schema\Validation;

abstract class Nested
{
    protected $baseValidator;

    protected $childValidator;

    protected $messages = [];

    /**
     * Collection constructor.
     * @param $baseValidator
     */
    public function __construct($baseValidator)
    {
        $this->baseValidator = $baseValidator;
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
     * @param $input
     * @return bool
     */
    abstract public function run($input): bool;
}