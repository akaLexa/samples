<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 30.03.2018
 *
 **/

namespace mwce\Validator;

class Sanitizer
{
    public static function sanitize($value, string $type)
    {
        return isset($value) && '' !== $value ? (new $type)($value) : null;
    }
}