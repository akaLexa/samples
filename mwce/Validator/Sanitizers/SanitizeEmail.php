<?php
/**
 * MuWebCloneEngine
 * Version: 1.6
 * User: epmak
 * 02.04.2018
 *
 **/

namespace mwce\Validator\Sanitizers;
class SanitizeEmail extends Sanitize
{

    /**
     * @param $value
     * @return mixed
     */
    public function sanitize($value) : string
    {
        if(empty($value) || $value === ''){
            return null;
        }
        
        return filter_var($value, \FILTER_SANITIZE_EMAIL);
    }
}