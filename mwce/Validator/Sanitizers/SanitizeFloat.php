<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Sanitizers;

class SanitizeFloat extends Sanitize
{
    /**
     * @param $value
     * @return mixed
     */
    public function sanitize($value)
    {
        return (float)$value;
    }
}