<?php

namespace Api\Schema\Validation;

class Map extends Nested
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

        if ($input !== null && !$this->childValidator->run($input)) {
            $this->messages = $this->childValidator->getMessages();

            return false;
        }

        return true;
    }
}