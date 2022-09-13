<?php

namespace Api\Schema\Validation;

use Aggregate\Map;

class Collection extends Nested
{
    /**
     * @param mixed $input
     * @return bool
     */
    public function run(mixed $input): bool
    {
        $this->clearMessages();

        if (!$this->baseValidator->run($input)) {
            $this->messages = $this->baseValidator->getMessages();

            return false;
        }

        if ($input instanceof Map) {
            $input = $input->toArray();
        }

        foreach ($input as $key => $item) {
            if (!$this->childValidator->run($item)) {
                if (!array_key_exists('items', $this->messages)) {
                    $this->messages['items'] = [];
                }

                // TODO move camel casing to representation class
                $this->messages['items'][] = [
                    'index' => $key,
                    'errorMessages' => $this->childValidator->getMessages()
                ];
            }
        }

        if ($this->messages) {
            return false;
        }

        return true;
    }
}
