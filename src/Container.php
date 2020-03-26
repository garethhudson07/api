<?php

namespace Api;

use League\Container\Container as BaseContainer;
use League\Container\ReflectionContainer;

class Container extends BaseContainer
{
    protected static $aliases = [];

    /**
     * @param string $alias
     * @param string $id
     */
    public static function alias(string $alias, string $id)
    {
        static::$aliases[$alias] = $id;
    }

    /**
     * @param string $alias
     * @return bool
     */
    public static function hasAlias(string $alias)
    {
        return array_key_exists($alias, static::$aliases);
    }

    /**
     * @param string $alias
     * @return mixed
     */
    public static function getIdByAlias(string $alias)
    {
        return static::$aliases[$alias];
    }

    /**
     * @param $id
     * @param bool $new
     * @return array|mixed|object
     */
    public function get($id, bool $new = false)
    {
        if (static::hasAlias($id)) {
            $id = static::getIdByAlias($id);
        }

        if (!$this->has($id)) {
            $this->delegate(new ReflectionContainer());
        }

        return parent::get($id, $new);
    }
}
