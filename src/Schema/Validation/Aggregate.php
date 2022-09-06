<?php

namespace Api\Schema\Validation;

use Aggregate\Map as AggregateMap;
use Api\Support\Str;

class Aggregate extends Map
{
    /**
     * @var array
     */
    protected $messages;

    /**
     * @param AggregateMap $input
     * @return bool
     */
    public function run(AggregateMap $input): bool
    {
        $this->messages = [];

        foreach ($this->items as $key => $validator) {
            if (!$input->has($key) && $validator->isSometimes()) {
                continue;
            }

            if ($validator->hasHook('before')) {
                // We need a clone of our validator object so that different collection items of the same time
                // can have individual validation rules
                $validator = clone $validator;
                $validator->callHook('before', $input);
            }

            $value = $input->get($key);

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
