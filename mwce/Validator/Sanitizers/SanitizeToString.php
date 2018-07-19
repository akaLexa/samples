<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Sanitizers;

class SanitizeToString extends Sanitize
{
    /**
     * @param $value
     * @return mixed
     */
    public function sanitize($value)
    {
        return htmlspecialchars(strip_tags($value),ENT_QUOTES);
    }
}