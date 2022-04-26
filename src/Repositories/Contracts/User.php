<?php

namespace Api\Repositories\Contracts;

use Api\Result\Contracts\Record;

interface User
{
    /**
     * @param string $id
     * @return Record|null
     */
    public function getById(string $id): ?Record;

    /**
     * @param string $username
     * @param string $password
     * @return null|string
     */
    public function getIdByCredentials(string $username, string $password): ?string;
}
