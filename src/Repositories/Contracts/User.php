<?php

namespace Api\Repositories\Contracts;

interface User
{
    /**
     * @param string $id
     * @return array|null
     */
    public function getById(string $id): ?array;

    /**
     * @param string $username
     * @param string $password
     * @return null|string
     */
    public function getIdByCredentials(string $username, string $password): ?string;
}