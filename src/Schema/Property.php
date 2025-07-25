<?php

namespace Api\Schema;

use Api\Schema\Validation\Validator;
use Api\Schema\Validation\Nested as NestedValidator;
use Api\Schema\Validation\Factory as ValidatorFactory;
use Closure;

/**
 * Class Property
 * @package Api\Schema
 * @method Property sometimes(bool $value = true)
 * @method Property required(bool $value = true)
 * @method Property nullable(bool $value = true)
 * @method Property minLength(int $minLength, ?Closure $configureRule = null)
 * @method Property maxLength(int $minLength, ?Closure $configureRule = null)
 * @method Property alpha(?Closure $configureRule = null)
 * @method Property alphaNumeric(?Closure $configureRule = null)
 * @method Property between($start, $end, ?Closure $configureRule = null)
 * @method Property min($minimum, ?Closure $configureRule = null)
 * @method Property max($maximum, ?Closure $configureRule = null)
 * @method Property email(?Closure $configureRule = null)
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
     * @var Validator|NestedValidator
     */
    protected Validator|NestedValidator $validator;

    /**
     * @var mixed
     */
    protected mixed $accepts = null;

    /**
     * @var array
     */
    protected array $meta = [];

    /**
     * @var Schema|null
     */
    protected ?Schema $schema = null;

    /**
     * Property constructor.
     * @param string $name
     * @param string $type
     * @param $validator
     */
    public function __construct(string $name, string $type, Validator|NestedValidator $validator)
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
     * @return Validator|NestedValidator
     */
    public function getValidator(): Validator|NestedValidator
    {
        return $this->validator;
    }

    /**
     * @return mixed
     */
    public function getAccepts(): mixed
    {
        return $this->accepts;
    }

    /**
     * @return NestedValidator|Validator
     */
    public function resolveValidator(): Validator|NestedValidator
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
     * @return static
     */
    public function beforeValidation(Closure $callback): static
    {
        $this->validator->before($callback);

        return $this;
    }

    /**
     * @param $arg
     * @return static
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
     * @return static
     */
    public function when($condition, Closure $callback): static
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function meta(string $name, mixed $value): static
    {
        $this->meta[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasMeta(string $name): bool
    {
        return array_key_exists($name, $this->meta);
    }



    /**
     * @param $name
     * @param $arguments
     * @return self
     */
    public function __call($name, $arguments): static
    {
        if ($this->validator->hasRule($name)) {
            $this->validator->{$name}(...$arguments);
        }

        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name): mixed
    {
        return $this->meta[$name] ?? null;
    }

    /**
     * @return Schema|null
     */
    public function getSchema(): ?Schema
    {
        return $this->schema;
    }

    /**
     * @param  Schema|null $schema
     * @return  static
     */
    public function setSchema(Schema $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @return static
     */
    public function primary(): static
    {
        if ($this->schema) {
            $this->schema->getKeyChain()->setPrimary($this);
        }

        return $this;
    }
}
