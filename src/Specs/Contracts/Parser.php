<?php

namespace Api\Specs\Contracts;

use Api\Queries\Relations;

interface Parser
{
    /**
     * @param string $input
     * @return array
     */
    public function fields(string $input): array;

    /**
     * @param string $input
     * @return Relations
     */
    public function relations(string $input): Relations;

    /**
     * @param string $input
     * @return \Oilstone\RsqlParser\Expression
     * @throws \Oilstone\RsqlParser\Exceptions\InvalidQueryStringException
     */
    public function filters(string $input);

    /**
     * @param string $input
     * @return array
     */
    public function sort(string $input): array;
}
