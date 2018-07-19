<?php

namespace mwce\Traits;

trait tSingleton
{
    /**
     * @var self instance
     */
    protected static $inst;

    /**
     * точка входа
     * @param null|mixed $params
     * @return self|tSingleton
     */
    public static function start($params = null)
    {
        if(null === self::$inst)
            self::$inst = new self($params);
        return self::$inst;
    }
}