<?php

namespace Api\Schema\Validation;

use Aggregate\Map as AggregateMap;

class Map extends Nested
{
    /**
     * @param AggregateMap|null $input
     * @return bool
     */
    public function run(?AggregateMap $input): bool
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
