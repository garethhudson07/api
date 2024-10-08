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
        'callback' => 'Callback',
        'checked' => 'TrueVal',
        'date' => 'Date',
        'decimals' => 'Decimal',
        'email' => 'Email',
        'max' => 'Max',
        'maxAge' => 'MaxAge',
        'min' => 'Min',
        'uuid' => 'Uuid',
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
        'alpha' => 'must only contain characters (a-z, A-Z)',
        'alphaNumeric' => 'must only contain characters (a-z, A-Z, 0-9)',
        'between' => 'must be between {{minValue}} and {{maxValue}}',
        'callback' => 'must be valid',
        'checked' => 'must be checked',
        'date' => 'must be a valid date',
        'decimals' => 'must be {{decimals}} decimal places',
        'email' => 'must be a valid email',
        'max' => 'must be a maximum of {{interval}}',
        'maxAge' => 'must be {{age}} years or less',
        'maxLength' => 'must be a maximum of {{maxValue}} characters',
        'min' => 'must be a minimum of {{interval}}',
        'minLength' => 'must be a minimum of {{minValue}} characters',
        'required' => 'required',
        'type' => 'must be of type {{type}}',
        'uuid' => 'must be a valid UUID',
    ];

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var bool
     */
    protected $required = false;

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
     * @var array
     */
    protected $hooks = [
        'before' => null
    ];

    /**
     * Validator constructor.
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return $this
     */
    public function required(): self
    {
        $this->required = true;

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
            $this->rules[] = $this->makeRule(
                'minLength',
                'Length',
                array_filter([$length, $callback])
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
            $this->rules[] = $this->makeRule(
                'maxLength',
                'Length',
                array_merge([null, $length], array_filter([$callback]))
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
            $this->rules[] = $this->makeRule($name, $this::RULE_MAP[$name], $arguments);
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

        if ($arguments && count($arguments) > ($key === 'callback' ? 1 : 0) && $arguments[count($arguments) - 1] instanceof Closure) {
            $callback = array_pop($arguments);
        }

        $instance = (new $class(...$arguments));

        if ($key !== 'callback') {
            $instance->setTemplate($this::MESSAGE_TEMPLATES[$key]);
        }

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
        // Validate required in isolation as we don't want additional
        // errors if the attribute is missing altogether
        if ($this->required && !$this->assert(
            new Rules\AllOf((new Rules\NotOptional())->setTemplate($this::MESSAGE_TEMPLATES['required'])),
            $this->type === 'string' ? trim($value) : $value
        )) {
            return false;
        }

        $rules = new Rules\AllOf(...$this->rules);

        try {
            $rules->addRule(new Rules\Type($this->type));
        } catch (ComponentException $e) {
            // Ignore unknown validation types
        }

        if ($this->nullable) {
            $rules = (new Rules\Nullable($rules));
        }

        return $this->assert($rules, $value);
    }

    /**
     * @param $rules
     * @param $value
     * @return bool
     */
    protected function assert($rules, $value): bool
    {
        try {
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

    /**
     * @param Closure $callback
     * @return $this
     */
    public function before(Closure $callback): self
    {
        $this->hooks['before'] = $callback;

        return $this;
    }

    /**
     * @param string $name
     * @param $data
     * @return $this
     */
    public function callHook(string $name, $data): self
    {
        if (!$this->hasHook($name)) {
            return $this;
        }

        $this->hooks[$name]($this, $data);

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHook(string $name): bool
    {
        return $this->hooks[$name] !== null;
    }
}
