<?php

namespace Api\Schema;

use Api\Schema\Validation\Validator;
use Api\Schema\Validation\Nested as NestedValidator;
use Api\Schema\Validation\Factory as ValidatorFactory;
use Closure;

/**
 * Class Property
 * @package Api\Schema
 * @method Property primary()
 * @method Property increments()
 * @method Property references(string $name)
 * @method Property on(string $table)
 * @method Property required()
 * @method Property minLength(int $minLength)
 * @method Property maxLength(int $minLength)
 * @method Property alpha()
 * @method Property alphaNumeric()
 * @method Property between($start, $end)
 * @method Property min($minimum)
 * @method Property max($maximum)
 * @method Property email()
 * @method Property nullable()
 * @method Property date(string $format = 'Y-m-d')
 * @method Property decimals(int $places)
 * @method Property uuid()
 * @method Property checked()
 */
class Property
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var mixed
     */
    protected $accepts;

    /**
     * Property constructor.
     * @param string $name
     * @param $validator
     */
    public function __construct(string $name, $validator)
    {
        $this->name = $name;
        $this->validator = $validator;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getAccepts()
    {
        return $this->accepts;
    }

    /**
     * @return NestedValidator|Validator
     */
    public function resolveValidator()
    {
        if ($this->validator instanceof NestedValidator && $this->accepts) {
            $this->validator->childValidator(
                $this->accepts instanceof Schema ? $this->accepts->resolveValidator() : ValidatorFactory::make($this->accepts)
            );
        }

        return $this->validator;
    }

    /**
     * @param $arg
     * @return $this
     */
    public function accepts($arg): Property
    {
        if ($arg instanceof Closure) {
            $this->accepts = new schema();
            $arg($this->accepts);

            return $this;
        }

        $this->accepts = $arg;

        return $this;
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
     * @param $name
     * @param $arguments
     * @return self
     */
    public function __call($name, $arguments): self
    {
        if ($this->validator->hasRule($name)) {
            $this->validator->{$name}(...$arguments);
        }

        return $this;
    }
}
