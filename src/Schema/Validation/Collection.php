<?php

namespace Api\Schema\Validation;

class Collection extends Nested
{
    /**
     * @param $input
     * @return bool
     */
    public function run($input): bool
    {
        $this->clearMessages();

        if (!$this->baseValidator->run($input)) {
            $this->messages = $this->baseValidator->getMessages();

            return false;
        }

        foreach ($input as $key => $item) {
            if (!$this->childValidator->run($item)) {
                if (!array_key_exists('items', $this->messages)) {
                    $this->messages['items'] = [];
                }

                $this->messages['items'][] = [
                    'index' => $key,
                    'error_messages' => $this->childValidator->getMessages()
                ];
            }
        }

        if ($this->messages) {
            return false;
        }

        return true;
    }
}