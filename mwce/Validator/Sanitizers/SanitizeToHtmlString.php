<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 29.03.2018
 *
 **/

namespace mwce\Validator\Sanitizers;

class SanitizeToHtmlString extends Sanitize
{

    /**
     * @param $value
     * @return mixed
     */
    public function sanitize($value)
    {
        return htmlspecialchars_decode($value, ENT_QUOTES);
    }
}