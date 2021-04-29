<?php

namespace Api\Schema\Validation;

use Closure;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules;

/**
 * Class Validator
 * @package Api\Schema\Validation
 */
class Validator
{
    /**
     * @const array
     */
    protected const LOCAL_RULES = [
        'required',
        'nullable',
        'sometimes',
        'minLength',
        'maxLength',
    ];

    /**
     * @const array
     */
    protected const RULE_MAP = [
        'alpha' => 'Alpha',
        'alphaNumeric' => 'Alnum',
        'between' => 'Between',
        'min' => 'Min',
        'max' => 'Max',
        'maxAge' => 'MaxAge',
        'email' => 'Email',
        'date' => 'Date',
        'decimals' => 'Decimal',
        'uuid' => 'Uuid',
        'checked' => 'TrueVal',
    ];

    /**
     * @const array
     */
    protected const TYPE_MAP = [
        'boolean' => 'bool',
        'integer' => 'int',
        'decimal' => 'float',
    ];

    /**
     * @const array
     */
    protected const MESSAGE_TEMPLATES = [
        'age' => 'must be a maximum of {{maxAge}} years',
        'type' => 'must be of type {{type}}',
        'required' => 'required',
        'alpha' => 'must only contain characters (a-z, A-Z)',
        'alphaNumeric' => 'must only contain characters (a-z, A-Z, 0-9)',
        'between' => 'must be between {{minValue}} and {{maxValue}}',
        'min' => 'must be a minimum of {{interval}}',
        'max' => 'must be a maximum of {{interval}}',
        'minLength' => 'must be a minimum of {{minValue}} characters',
        'maxLength' => 'must be a maximum of {{maxValue}} characters',
        'maxAge' => 'must be {{age}} years or less',
        'email' => 'must be a valid email',
        'date' => 'must be a valid date',
        'decimals' => 'must be {{decimals}} decimal places',
        'uuid' => 'must be a valid UUID',
        'checked' => 'must be checked',
    ];

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Rules\AllOf
     */
    protected $rules;

    /**
     * @var bool
     */
    protected $nullable = false;

    /**
     * @var bool
     */
    protected $sometimes = false;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * Validator constructor.
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
        $this->rules = new Rules\AllOf();
    }

    /**
     * @return $this
     */
    public function required(): self
    {
        $this->rules->addRule(
            (new Rules\NotEmpty())->setTemplate($this::MESSAGE_TEMPLATES['required'])
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function nullable(): self
    {
        $this->nullable = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function sometimes(): self
    {
        $this->sometimes = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSometimes(): bool
    {
        return $this->sometimes;
    }

    /**
     * @param int $length
     * @param Closure|null $callback
     * @return $this
     */
    public function minLength(int $length, ?Closure $callback = null): self
    {
        try {
            $this->rules->addRule(
                $this->makeRule(
                    'minLength',
                    'Length',
                    array_filter([$length, $callback])
                )
            );
        } catch (ComponentException $e) {
            // Ignore invalid length rules
        }

        return $this;
    }

    /**
     * @param int $length
     * @param Closure|null $callback
     * @return $this
     */
    public function maxLength(int $length, ?Closure $callback = null): self
    {
        try {
            $this->rules->addRule(
                $this->makeRule(
                    'maxLength',
                    'Length',
                    array_merge([null, $length], array_filter([$callback]))
                )
            );
        } catch (ComponentException $e) {
            // Ignore invalid length rules
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return $this
     */
    public function __call(string $name, array $arguments): self
    {
        if ($this->hasRule($name)) {
            $this->rules->addRule(
                $this->makeRule($name, $this::RULE_MAP[$name], $arguments)
            );
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string $class
     * @param array $arguments
     * @return mixed
     */
    protected function makeRule(string $key, string $class, array $arguments)
    {
        $class = 'Respect\\Validation\\Rules\\' . $class;
        $callback = null;

        if ($arguments && $arguments[count($arguments) - 1] instanceof Closure) {
            $callback = array_pop($arguments);
        }

        $instance = (new $class(...$arguments))->setTemplate($this::MESSAGE_TEMPLATES[$key]);

        if ($callback) {
            $callback($instance);
        }

        return $instance;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasRule(string $name): bool
    {
        return in_array($name, $this::LOCAL_RULES) || array_key_exists($name, $this::RULE_MAP);
    }

    /**
     * @param $value
     * @return bool
     */
    public function run($value): bool
    {
        try {
            $rules = new Rules\AllOf(...$this->rules->getRules());

            try {
                $rules->addRule(new Rules\Type($this->type));
            } catch (ComponentException $e) {
                // Ignore unknown validation types
            }

            if ($this->nullable) {
                $rules = (new Rules\Nullable($rules));
            }

            $rules->assert($value);
        } catch (NestedValidationException $e) {
            $this->messages = array_values(
                array_filter($e->getMessages())
            );

            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
