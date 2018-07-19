<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Sanitizers;

class SanitizeArray extends Sanitize
{

    /**
     * @param $value
     * @return mixed
     */
    public function sanitize($value)
    {
        if(empty($value)) {
            return null;
        }

        if (\is_array($value) || $value instanceof \ArrayAccess) {
            return $value;
        }

        return (array)$value;
    }
}