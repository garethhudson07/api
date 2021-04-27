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
