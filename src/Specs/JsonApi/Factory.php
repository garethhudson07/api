<?php

namespace Api\Specs\JsonApi;

use Api\Config\Service;

class Factory
{
    /**
     * @return mixed
     */
    public static function config()
    {
        return (new Service())->accepts(
            'relationsKey',
            'fieldsKey',
            'filtersKey',
            'sortKey',
            'offsetKey',
            'limitKey'
        )
            ->relationsKey('include')
            ->fieldsKey('fields')
            ->filtersKey('filter')
            ->sortKey('sort')
            ->offsetKey('offset')
            ->limitKey('limit');
    }
}
