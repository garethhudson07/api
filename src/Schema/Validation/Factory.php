<?php

namespace Api\Schema\Validation;

class Factory {
    public static function make(string $type)
    {
        switch ($type) {
            case 'schema':
                return new Map(
                    new Validator('array')
                );

            case 'collection':
                return new Collection(
                    new Validator('array')
                );

            default:
                return new Validator($type);
        }
    }
}