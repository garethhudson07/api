<?php

namespace Api\Schema\Validation;

use Api\Collection;
use Api\Support\Str;

/**
 * Class Aggregate
 * @package Api\Schema\Validation
 */
class Aggregate extends Collection
{
    /**
     * @var array
     */
    protected $messages;

    /**
     * @param array $input
     * @return bool
     */
    public function run(array $input): bool
    {
        $this->messages = [];

        foreach ($this->items as $key => $validator) {
            if (!array_key_exists($key, $input) && $validator->isSometimes()) {
                continue;
            }

            if ($validator->hasHook('before')) {
                // We need a clone of our validator object so that different collection items of the same time
                // can have individual validation rules
                $validator = clone $validator;
                $validator->callHook('before', $input);
            }

            $value = $input[$key] ?? null;

            // TODO move camel casing to dedicated representation class
            if (!$validator->run($value)) {
                $this->messages[Str::camel($key)] = $validator->getMessages();
            }
        }

        if (count($this->messages) > 0) {
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
