<?php

namespace Api\Schema\Validation;

use Api\Collection;

class Aggregate extends Collection
{
    protected $messages;

    public function run(array $input)
    {
        $this->messages = [];

        foreach ($this->items as $key => $validator) {
            $value = $input[$key] ?? null;

            if (!$validator->run($value)) {
                $this->messages[$key] = $validator->getMessages();
            }
        }

        if (count($this->messages) > 0) {
            throw (new ValidationException('The supplied resource is invalid.'))->data([
                'attributes' => $this->messages
            ]);
        }

        return true;
    }
}
