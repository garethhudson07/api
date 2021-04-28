<?php

namespace Api\Schema\Validation;

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
        'minLength',
        'maxLength'
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
        'email' => 'Email'
    ];

    /**
     * @const array
     */
    protected const MESSAGE_TEMPLATES = [
        'type' => 'Must be of type {{type}}',
        'notEmpty' => 'This field is required',
        'alpha' => 'Must only contain characters (a-z, A-Z)',
        'alphaNumeric' => 'Must only contain characters (a-z, A-Z, 0-9)',
        'between' => 'must be between {{minValue}} and {{maxValue}}',
        'minLength' => 'Must be a minimum of {{minValue}} characters',
        'maxLength' => 'Must be a maximum of {{maxValue}} characters',
        'min' => 'Must be a minimum of {{interval}}',
        'max' => 'Must be a maximum of {{interval}}',
        'email' => 'Must be a valid email'
    ];

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $nullable = false;

    /**
     * @var Rules\AllOf
     */
    protected $rules;

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

        try {
            $this->rules->addRule(new Rules\Type($type));
        } catch (ComponentException $e) {
            // Ignore unknown validation types
        }
    }

    /**
     * @return $this
     */
    public function required(): self
    {
        $this->rules->addRule(new Rules\NotEmpty());

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
     * @param int $length
     * @return self
     */
    public function minLength(int $length): self
    {
        try {
            $this->rules->addRule(
                (new Rules\Length($length))->setName('minLength')
            );
        } catch (ComponentException $e) {
            // Ignore invalid length rules
        }

        return $this;
    }

    /**
     * @param int $length
     * @return self
     */
    public function maxLength(int $length): self
    {
        try {
            $this->rules->addRule(
                (new Rules\Length(null, $length))->setName('maxLength')
            );
        } catch (ComponentException $e) {
            // Ignore invalid length rules
        }

        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this|bool
     */
    public function __call($name, $arguments)
    {
        if ($this->hasRule($name)) {
            $class = 'Respect\\Validation\\Rules\\' . $this::RULE_MAP[$name];

            $this->rules->addRule(
                (new $class(...$arguments))->setName($name)
            );

            return $this;
        }

        return false;
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
            if ($this->nullable) {
                (new Rules\Nullable($this->rules))->assert($value);

                return true;
            }

            $this->rules->assert($value);
        } catch (NestedValidationException $e) {
            $this->messages = array_values(
                array_filter($e->getMessages($this::MESSAGE_TEMPLATES))
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
