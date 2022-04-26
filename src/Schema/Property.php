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
 * @method Property sometimes()
 * @method Property required()
 * @method Property minLength(int $minLength, ?Closure $configureRule = null)
 * @method Property maxLength(int $minLength, ?Closure $configureRule = null)
 * @method Property alpha(?Closure $configureRule = null)
 * @method Property alphaNumeric(?Closure $configureRule = null)
 * @method Property between($start, $end, ?Closure $configureRule = null)
 * @method Property min($minimum, ?Closure $configureRule = null)
 * @method Property max($maximum, ?Closure $configureRule = null)
 * @method Property email(?Closure $configureRule = null)
 * @method Property nullable(?Closure $configureRule = null)
 * @method Property date(string $format = 'Y-m-d', ?Closure $configureRule = null)
 * @method Property decimals(int $places, ?Closure $configureRule = null)
 * @method Property uuid(?Closure $configureRule = null)
 * @method Property checked(?Closure $configureRule = null)
 * @method Property maxAge(int $maxAge, ?Closure $configureRule = null)
 */
class Property
{
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var Validator
     */
    protected Validator $validator;

    /**
     * @var mixed
     */
    protected $accepts;

    /**
     * Property constructor.
     * @param string $name
     * @param string $type
     * @param $validator
     */
    public function __construct(string $name, string $type, $validator)
    {
        $this->name = $name;
        $this->type = $type;
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
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
     * @param Closure $callback
     * @return $this
     */
    public function beforeValidation(Closure $callback): self
    {
        $this->validator->before($callback);

        return $this;
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
