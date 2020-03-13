<?php

namespace Api\Http\Requests;

class Parser
{
    /**
     * @param string $input
     * @return array
     */
    public static function segments(string $input)
    {
        return array_values(array_filter(explode('/', $input)));
    }
}
