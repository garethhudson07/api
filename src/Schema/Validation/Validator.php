<?php

namespace Api\Schema\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules;

class Validator
{
    protected $type;

    protected $required = false;

    protected $rules;

    protected $messages;

    protected const LOCAL_RULES = [
        'required',
        'minLength',
        'maxLength'
    ];

    protected const RULE_MAP = [
        'alpha' => 'Alpha',
        'alphaNumeric' => 'Alnum',
        'between' => 'Between',
        'min' => 'Min',
        'max' => 'Max',
        'email' => 'Email'
    ];

    protected const MESSAGE_TEMPLATES = [
        'type' => 'Must be of type {{type}}',
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
     * Validator constructor.
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
        $this->rules = new Rules\AllOf();
        $this->rules->addRule(new Rules\Type($type));
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasRule(string $name)
    {
        return in_array($name, $this::LOCAL_RULES) || array_key_exists($name, $this::RULE_MAP);
    }

    /**
     * @return $this
     */
    public function required()
    {
        $this->required = true;

        return $this;
    }

    /**
     * @param int $length
     * @return $this
     */
    public function minLength(int $length)
    {
        $this->rules->addRule(
            (new Rules\Length($length))->setName('minLength')
        );

        return $this;
    }

    /**
     * @param int $length
     * @return $this
     */
    public function maxLength(int $length)
    {
        $this->rules->addRule(
            (new Rules\Length(null, $length))->setName('maxLength')
        );

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
     * @param $value
     * @return bool
     */
    public function run($value)
    {
        try {
            if (!$this->required) {
                return (new Rules\Optional($this->rules))->assert($value);
            }

            return $this->rules->assert($value);
        } catch (NestedValidationException $e) {
            $this->messages = array_values(
                array_filter($e->findMessages($this::MESSAGE_TEMPLATES))
            );

            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
