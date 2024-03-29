<?php

namespace Api\Specs\JsonApi;

use Api\Config\Store;

class Factory
{
    /**
     * @return mixed
     */
    public static function config()
    {
        return (new Store())->accepts(
            'relationsKey',
            'fieldsKey',
            'filtersKey',
            'sortKey',
            'offsetKey',
            'limitKey',
            'pageKey',
            'searchKey',
        )
            ->relationsKey('include')
            ->fieldsKey('fields')
            ->filtersKey('filter')
            ->sortKey('sort')
            ->offsetKey('offset')
            ->limitKey('limit')
            ->searchKey('search')
            ->pageKey('page');
    }
}
